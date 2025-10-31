<?php

namespace App\Filament\Resources\SavedReportResource\Pages;

use App\Filament\Resources\SavedReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSavedReports extends ManageRecords
{
    protected static string $resource = SavedReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
