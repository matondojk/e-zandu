<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect; // Já não é estritamente necessário

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        // Usa o método estático getRecord() do Resource para decidir se deve mostrar o botão "Criar"
        return [
            Actions\CreateAction::make()
                ->label('Criar Configuração')
                ->visible(fn () => !SettingResource::getRecord()),
        ];
    }
    
    // MÉTODO DE REDIRECIONAMENTO CRUCIAL
    protected function getRedirectUrl(): string
    {
        // Tenta encontrar o registro de configuração (assumindo ID=1)
        $record = SettingResource::getRecord();
        
        if ($record) {
             // Redireciona para a página de edição do registro existente
             return SettingResource::getUrl('edit', ['record' => $record]);
        }
        
        // Se não houver registro (ID 1), redireciona para criar um novo
        return SettingResource::getUrl('create');
    }

    public function mount(): void
    {
        // Chamar o mount da classe pai (ListRecords) não é necessário aqui
        // parent::mount(); 

        $url = $this->getRedirectUrl();
        
        // Se a URL de redirecionamento for diferente da URL de índice atual, redireciona
        if ($url !== $this->getResource()::getUrl('index')) {
            // CORREÇÃO CRÍTICA: Use a função global redirect() para ser compatível com Livewire
            redirect($url);
            // O Livewire trata o redirecionamento sem a necessidade do ->send() explícito
        }
    }
}
