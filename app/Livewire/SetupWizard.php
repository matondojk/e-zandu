<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\WithFileUploads; // ✅ 1. Adicionar para uploads

class SetupWizard extends Component
{
    use WithFileUploads; // ✅ 2. Usar o trait

    // Controla o passo atual
    public int $currentStep = 1;

    // Array para os dados do formulário
    public array $data = [
        // Passo 1: Geral
        'store_name' => '',
        'nif' => '',
        'admin_phone' => '',
        'support_email' => '',
        'logo_path' => null, // ✅ 3. Adicionar campo de logo
        'icon_path' => null, // ✅ 4. Adicionar campo de ícone
        
        // Passo 2: Pagamentos e Stock
        'iban' => '',
        'multicaixa_express' => '',
        'shipping_rate' => 0,
        'minimum_stock' => 10,
        'stock_alert_emails' => '',

        // Passo 3: IA
        'company_description' => '',
        'product_types_text' => '', // Campo de texto simples
    ];

    /**
     * Define as regras de validação para cada passo.
     */
    protected function getRulesForStep(int $step): array
    {
        switch ($step) {
            case 1:
                return [
                    'data.store_name' => 'required|string|max:255',
                    'data.nif' => 'required|string|max:50',
                    'data.admin_phone' => 'required|string|max:20',
                    'data.support_email' => 'required|email|max:255',
                    // ✅ 5. Adicionar regras de validação para os uploads
                    'data.logo_path' => 'nullable|image|max:1024', // 1MB Max
                    'data.icon_path' => 'nullable|image|max:512',  // 512KB Max
                ];
            case 2:
                return [
                    'data.iban' => 'required|string|max:255',
                    'data.multicaixa_express' => 'required|string|max:50',
                    'data.shipping_rate' => 'required|numeric|min:0',
                    'data.minimum_stock' => 'required|numeric|min:0',
                    'data.stock_alert_emails' => 'nullable|string',
                ];
            case 3:
                 return [
                    'data.company_description' => 'required|string|min:20',
                    'data.product_types_text' => 'required|string|min:3', // Valida o texto
                 ];
            default:
                return [];
        }
    }

    /**
     * Avança para o próximo passo após validar.
     */
    public function nextStep()
    {
        $this->validate($this->getRulesForStep($this->currentStep));

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    /**
     * Volta um passo.
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Finaliza: Valida, salva, e redireciona para o painel.
     */
    public function save()
    {
        // Valida as regras do último passo
        $this->validate($this->getRulesForStep($this->currentStep));

        try {
            // Prepara os dados finais (copia os dados de texto)
            $finalData = $this->data;
            
            // ✅ 6. Lógica para salvar os ficheiros
            if ($this->data['logo_path']) {
                // Salva o ficheiro no disco 'public' (storage/app/public/settings)
                // e armazena apenas o caminho no array $finalData.
                $finalData['logo_path'] = $this->data['logo_path']->store('settings', 'public');
            }
            if ($this->data['icon_path']) {
                $finalData['icon_path'] = $this->data['icon_path']->store('settings', 'public');
            }
            
            // Converte o texto 'product_types_text' num array JSON
            $finalData['product_types'] = collect(explode(',', $this->data['product_types_text']))
                ->map(fn($item) => trim($item))
                ->filter() // Remove itens vazios
                ->values()
                ->toArray();
            
            unset($finalData['product_types_text']); // Remove o campo de texto

            // Encontra o registo vazio que o 'ListSettings' criou
            $setting = Setting::first();
            
            // Atualiza o registo com os dados do assistente
            $setting->update($finalData);

            // Redireciona de volta para o Dashboard do Filament
            return redirect(route('filament.admin.pages.dashboard'));

        } catch (\Exception $e) {
            Log::error("Falha ao salvar SetupWizard: " . $e->getMessage());
            // Adiciona um erro genérico para o utilizador
            $this->addError('general', 'Não foi possível salvar as configurações. Verifique os logs.');
        }
    }

    /**
     * Renderiza a view e diz ao Livewire para usar o nosso layout 'setup'.
     */
    public function render()
    {
        return view('livewire.setup-wizard')
            ->layout('layouts.setup'); // <-- Diz ao Livewire para usar o nosso layout limpo
    }
}