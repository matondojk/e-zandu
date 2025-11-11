<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\Setting; // ✅ Importar o Model

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;


    public function mount(): void
    {

        $setting = Setting::first();

        $this->redirect(SettingResource::getUrl('edit', ['record' => $setting->id]));
    }
}
