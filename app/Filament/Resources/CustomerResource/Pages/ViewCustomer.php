<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverview; // Importar Widget
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists; // Imports
use Filament\Infolists\Infolist;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Regista o Widget de Estatísticas no topo desta página.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            CustomerStatsOverview::class,
        ];
    }

    /**
     * ✅ CORREÇÃO: Define o layout sem guias (Tabs).
     * As secções são mostradas verticalmente.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ❌ O Infolists\Components\Tabs::make('Tabs') foi REMOVIDO.

                // Secção 1: Detalhes Principais
                Infolists\Components\Section::make('Informação de Contacto')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nome'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('delivery_address')
                            ->label('Endereço de Entrega')
                            ->columnSpanFull(),
                    ]),
                
                // Secção 2: Atributos de CRM (logo abaixo)
                Infolists\Components\Section::make('Segmentação e Notas')
                    ->schema([
                        Infolists\Components\TextEntry::make('tags')
                            ->label('Tags de Segmentação')
                            ->badge(), // (Corrigido para F3)
                        
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notas Internas')
                            ->placeholder('Sem notas...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}