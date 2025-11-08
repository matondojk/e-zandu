<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\CreateRecord; // Importação correta
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Campaign;
use App\Jobs\SendCampaignJob;
use App\Services\IntelligenceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateCampaign extends CreateRecord
{
    // 👇 ISTO É OBRIGATÓRIO PARA CORRIGIR A TELA BRANCA 👇
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = CampaignResource::class;

    /**
     * Esta função é o "coração" da sua lógica.
     * Ela corre DEPOIS que a Campanha é criada.
     */
    protected function afterCreate(): void
    {
        $campaign = $this->getRecord();
        $data = $this->getData(); // Pega TODOS os dados do formulário

        try {
            // Tenta buscar a audiência
            $customers = $this->getAudience($data);

            if ($customers->isEmpty()) {
                 $campaign->audience()->sync([]); // Garante que fica 0
                 Notification::make()->title('Campanha Criada (Sem Audiência)')
                    ->body("A campanha foi salva, mas nenhum cliente correspondeu aos filtros (verifique 'role=customer' na DB).")
                    ->warning()->send();
                return; // Pára a execução aqui
            }

            // Se encontrou clientes, salva-os na tabela 'campaign_audience'
            $customerIds = $customers->pluck('id');
            $campaign->audience()->sync($customerIds);
            
            // Verifica se é para agendar ou despachar agora
            $isScheduled = $campaign->scheduled_at && Carbon::parse($campaign->scheduled_at)->isFuture();

            if ($isScheduled) {
                // APENAS SALVA COMO "AGENDADA"
                $campaign->update(['status' => 'scheduled']);
                
                Notification::make()->title('Campanha Agendada!')
                    ->body("A campanha foi agendada para {$campaign->scheduled_at->format('d/m/Y H:i')} com {$customers->count()} clientes.")
                    ->success()->send();
            } else {
                // É PARA ENVIAR AGORA
                $campaign->update(['status' => 'processing']);
                
                foreach ($customers as $customer) {
                    SendCampaignJob::dispatch($campaign, $customer)
                        ->onQueue('campaigns');
                }
                
                Notification::make()->title('Campanha Criada e em Processamento!')
                    ->body("A campanha foi enviada para a fila para processar {$customers->count()} clientes.")
                    ->success()->send();
            }

        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);
            Notification::make()->title('Erro ao Processar Audiência')
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    /**
     * Esta função busca os clientes com base no formulário.
     */
    protected function getAudience(array $data): Collection
    {
        $audienceType = $data['audience_type'] ?? 'all';
        $aiService = app(IntelligenceService::class);

        // --- Opção 2: Manual ---
        if ($audienceType === 'manual') {
            if (empty($data['manual_customers'])) {
                return collect(); // Retorna uma coleção vazia
            }
            return User::whereIn('id', $data['manual_customers'])->where('role', 'customer')->get();
        }

        // --- Opção 3: Filtro por IA ---
        if ($audienceType === 'ai_prompt' && !empty($data['audience_prompt'])) {
            $prompt = $data['audience_prompt'];
            $sqlPrompt = "Com base no schema, gere uma query SQL para: '{$prompt}'. A query deve selecionar APENAS o 'id' da tabela 'users' (onde role='customer').";
            
            $sqlResult = $aiService->runChatQuery($sqlPrompt);
            
            if (empty($sqlResult['data'])) {
                Notification::make()->title('IA não encontrou clientes')->body("O prompt da IA não retornou clientes.")->warning()->send();
                return collect();
            }
            
            $userIds = collect($sqlResult['data'])->pluck('id');
            return User::whereIn('id', $userIds)->get(); 
        }

        // --- Opção 1: Todos (Default) ---
        if ($audienceType === 'all') {
            return User::where('role', 'customer')->get();
        }

        return collect();
    }

    /**
     * Redireciona de volta para a lista após criar.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}