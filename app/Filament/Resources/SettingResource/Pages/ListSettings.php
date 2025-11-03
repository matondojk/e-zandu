<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\Setting; // ✅ Importar o Model

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    /**
     * Esta função é o nosso "gatilho".
     * É executada antes de a página de lista ser renderizada.
     */
    public function mount(): void
    {
        // Tenta encontrar o primeiro registo. Se não existir, cria um VAZIO.
        // Isto garante que temos sempre um registo com um UUID para editar.
        $setting = Setting::firstOrCreate([], []); 
        
        // Se o 'store_name' (um campo obrigatório do setup) estiver vazio,
        // significa que o setup nunca foi corrido.
        if (empty($setting->store_name)) {
            
            // ❌ Redireciona para a nossa rota de setup (fora do Filament)
            $this->redirect(route('setup.index'));

        } else {
            
            // ✅ Se já está configurado, redireciona para a página de edição normal
            $this->redirect(SettingResource::getUrl('edit', ['record' => $setting->id]));
        }
    }
}