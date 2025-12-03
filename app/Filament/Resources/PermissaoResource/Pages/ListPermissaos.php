<?php

namespace App\Filament\Resources\PermissaoResource\Pages;

use App\Filament\Resources\PermissaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissaos extends ListRecords
{
    protected static string $resource = PermissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
