<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Newsletter;
use App\Jobs\SendCampaignJob;
use App\Services\IntelligenceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function afterCreate(): void
    {
        $campaign = $this->getRecord();
        $data = $this->data;

        // 1. Sincroniza Produtos (Pivot)
        if ($campaign->content_type === 'product_promo' && !empty($data['productsInPromotion'])) {
            $syncData = collect($data['productsInPromotion'])->mapWithKeys(function ($item) {
                return [
                    $item['product_id'] => ['promotional_price' => $item['promotional_price']]
                ];
            })->toArray();
            
            $campaign->productsInPromotion()->sync($syncData);
        }

        try {
            // 2. Busca a Audiência
            $customers = $this->getAudience($data);

            if ($customers->isEmpty()) {
                 // Se não achou ninguém, volta para rascunho para evitar travamento
                 $campaign->update(['status' => 'draft']);
                 $campaign->audience()->sync([]); 
                 Notification::make()->title('Aviso: Audiência Vazia')
                    ->body("A campanha foi salva como rascunho porque nenhum destinatário foi encontrado.")
                    ->warning()->send();
                return;
            }

            // 3. Salva quem tem ID na tabela pivot (Usuários)
            // Assinantes de Newsletter (sem ID) são ignorados nesta etapa de sync
            $usersWithId = $customers->filter(fn($c) => !empty($c->id));
            
            if ($usersWithId->isNotEmpty()) {
                $campaign->audience()->sync($usersWithId->pluck('id'));
            }

            // 4. Lógica de Envio baseada no Status escolhido
            
            // STATUS: RASCUNHO
            if ($campaign->status == 'draft') {
                Notification::make()->title('Rascunho Salvo')
                    ->body("A campanha foi salva e pode ser editada posteriormente.")
                    ->success()->send();
                return;
            }

            // STATUS: AGENDADO
            if ($campaign->status == 'scheduled') {
                Notification::make()->title('Agendamento Realizado')
                    ->body("A campanha será enviada automaticamente em {$campaign->scheduled_at->format('d/m/Y H:i')}.")
                    ->success()->send();
                return; // O Job de CRON cuidará disso
            }

            // STATUS: ENVIAR AGORA (Processing)
            if ($campaign->status == 'processing') {
                // Dispara Jobs imediatamente
                foreach ($customers as $customer) {
                    SendCampaignJob::dispatch($campaign, $customer)
                        ->onQueue('campaigns');
                }
                
                $campaign->update(['status' => 'sent']);
                
                Notification::make()->title('Envio Iniciado!')
                    ->body("A campanha está sendo processada para {$customers->count()} destinatários.")
                    ->success()->send();
            }

        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);
            Notification::make()->title('Erro ao Processar')
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    protected function getAudience(array $data): Collection
    {
        $audienceType = $data['audience_type'] ?? 'all';
        $aiService = app(IntelligenceService::class);

        // Opção Newsletter
        if ($audienceType === 'newsletter') {
            return Newsletter::where('is_subscribed', true)->get()->map(function ($subscriber) {
                return (object) [
                    'id' => null,
                    'email' => $subscriber->email,
                    'name' => 'Assinante',
                    'phone' => null,
                ];
            });
        }
        
        // Opção Manual
        if ($audienceType === 'manual') {
            if (empty($data['manual_customers'])) {
                return collect();
            }
            return User::whereIn('id', $data['manual_customers'])->where('role', 'customer')->get();
        }

        // Opção IA
        if ($audienceType === 'ai_prompt' && !empty($data['audience_prompt'])) {
            $prompt = $data['audience_prompt'];
            $sqlPrompt = "Com base no schema, gere uma query SQL para: '{$prompt}'. A query deve selecionar APENAS o 'id' da tabela 'users' (onde role='customer').";
            
            $sqlResult = $aiService->runChatQuery($sqlPrompt);
            
            if (empty($sqlResult['data'])) {
                return collect();
            }
            
            $userIds = collect($sqlResult['data'])->pluck('id');
            return User::whereIn('id', $userIds)->get(); 
        }

        // Opção Todos (Default)
        if ($audienceType === 'all') {
            return User::where('role', 'customer')->get();
        }

        return collect();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}