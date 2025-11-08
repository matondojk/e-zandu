<?php
namespace App\Filament\Resources\ChangelogResource\Pages;

use App\Filament\Resources\ChangelogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewChangelog extends ViewRecord
{
    protected static string $resource = ChangelogResource::class;

    // Vazio. Herda o infolist() do Resource.
}