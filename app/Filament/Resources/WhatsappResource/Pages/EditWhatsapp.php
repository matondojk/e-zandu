<?php

namespace App\Filament\Resources\WhatsappResource\Pages;

use App\Filament\Resources\WhatsappResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsapp extends EditRecord
{
    protected static string $resource = WhatsappResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
