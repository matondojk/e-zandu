<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChangelogResource\Pages;
use App\Models\Changelog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;

class ChangelogResource extends Resource
{
    protected static ?string $model = Changelog::class;

    // --- CORREÇÃO DO ÍCONE ---
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square'; 
    // -------------------------
    
    // protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $modelLabel = 'Atualização';
    protected static ?string $pluralModelLabel = 'Atualizações';
    protected static ?int $navigationSort = 99;

    public static function getNavigationBadge(): ?string
    {
        // Encontra a última versão lançada
        return Changelog::latest('created_at')->value('version');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // --- CORREÇÃO DA COR ---
        return 'warning'; // Define a cor laranja
        // -----------------------
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Versão')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)->schema([
                    Infolists\Components\TextEntry::make('version')
                        ->label('Versão'),
                    Infolists\Components\TextEntry::make('title')
                        ->label('Título'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Data de Lançamento')
                        ->dateTime('d/m/Y'),
                ]),
                
                Infolists\Components\Section::make('Detalhes da Atualização')
                    ->schema([
                        // Usamos ViewEntry para carregar a Blade customizada
                        \Filament\Infolists\Components\ViewEntry::make('features')
                            ->label('')
                            ->view('filament.infolists.changelog-features'),
                    ])
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangelogs::route('/'),
            'view' => Pages\ViewChangelog::route('/{record}'),
        ];
    }
}