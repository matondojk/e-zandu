<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Spatie\Activitylog\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\Filters\SelectOption;
use Illuminate\Database\Eloquent\Builder; // <-- CORRIGIDO
use Illuminate\Support\HtmlString; 
use Filament\Tables\Filters\SelectFilter; // <-- CORRIGIDO
use Filament\Tables; // Para referenciar Tables\Filters

class ActivityLog extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Registro de Atividades'; // <-- TÍTULO CORRIGIDO
    protected static ?string $title = 'Registro de Atividades (Auditoria)'; // <-- TÍTULO CORRIGIDO
    protected static ?string $slug = 'activity-log';
    protected static ?string $navigationGroup = 'Sistema & Config';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.activity-log';

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('log_name')
                    ->label('Tipo de Log')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'info',
                        'product' => 'success',
                        'category' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Ação')
                    ->searchable()
                    ->sortable()
                    // Certifique-se de importar o Model Activity para o type hint
                    ->description(fn (Activity $record): string => "Modelo: {$record->subject_type}"), 

                TextColumn::make('causer.name')
                    ->label('Utilizador')
                    ->default('Sistema/Visitante')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('properties.old')
                    ->label('Detalhes da Alteração')
                    ->formatStateUsing(function (Activity $record) {
                        if ($record->description === 'created') {
                            return 'Novo registo criado';
                        }
                        
                        // --- Correções de compatibilidade PHP aplicadas nas iterações anteriores ---
                        $oldProperties = isset($record->properties['old']) ? $record->properties['old'] : [];
                        $newProperties = isset($record->properties['attributes']) ? $record->properties['attributes'] : [];

                        // Itera sobre as propriedades antigas.
                        $changes = collect($oldProperties)
                            ->map(fn($old, $key) => "{$key}: de '{$old}' para '" . (isset($newProperties[$key]) ? $newProperties[$key] : 'N/A') . "'")
                            ->implode('<br>');

                        return new HtmlString($changes ?: 'Sem detalhes de alteração.');
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro para o tipo de modelo (Product, Category, etc)
                // Agora referenciado corretamente via SelectFilter importado.
                SelectFilter::make('subject_type') 
                    ->label('Modelo')
                    ->options([
                        \App\Models\Product::class => 'Produto',
                        \App\Models\Category::class => 'Categoria',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $query, $value) => $query->where('subject_type', $value)
                    ))
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
