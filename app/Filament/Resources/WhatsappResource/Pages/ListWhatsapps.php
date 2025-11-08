<?php

namespace App\Filament\Resources\WhatsappResource\Pages;

use App\Filament\Resources\WhatsappResource;
use App\Models\WhatsAppSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsapps extends ListRecords
{
    protected static string $resource = WhatsappResource::class;

    /**
     * Corre antes da página ser renderizada.
     */
    public function mount(): void
    {
        // Tenta buscar a *única* linha de configuração
        $setting = WhatsAppSetting::first();

        if ($setting) {
            // Se existir, redireciona para a página de EDIÇÃO (o ID do registo é necessário)
            redirect(self::getResource()::getUrl('edit', ['record' => $setting->id]));
        } else {
            // Se NÃO existir, redireciona para a página de CRIAÇÃO
            redirect(self::getResource()::getUrl('create'));
        }
    }

    // Removemos os botões do cabeçalho, pois a página só redireciona
    protected function getHeaderActions(): array
    {
        return [];
    }
}