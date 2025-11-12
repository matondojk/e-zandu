<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use App\Filament\Resources\PurchaseResource\RelationManagers; 

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    // ✅ ESTA LINHA É OBRIGATÓRIA PARA REMOVER AS GUIAS EM ALGUNS CASOS
    protected static bool $hasRelationManagerTabsWithContent = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    // ✅ Mantemos este método também por segurança
    public function getRelationManagersConfiguration(): array
    {
        return [
            'tabs' => false,
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // --- CABEÇALHO DO DOCUMENTO ---
                Section::make('Detalhes da Compra')
                    ->columns(['default' => 1, 'md' => 4])
                    ->schema([
                        TextEntry::make('purchase_number')
                            ->label('Nº Compra')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-hashtag'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Aberta' => 'gray',
                                'Confirmada' => 'info',
                                'Recebida' => 'success',
                                'Cancelada' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('issue_date')
                            ->label('Emissão')
                            ->date('d/m/Y'),

                        TextEntry::make('expected_delivery_date')
                            ->label('Entrega Prevista')
                            ->date('d/m/Y')
                            ->placeholder('Não definido'),

                        TextEntry::make('user.name')
                            ->label('Responsável')
                            ->icon('heroicon-o-user'),
                        
                        TextEntry::make('payment_terms')
                            ->label('Condições'),
                            
                        TextEntry::make('payment_method')
                            ->label('Pagamento'),

                        IconEntry::make('is_delivered')
                            ->label('Entregue?')
                            ->boolean(),
                    ]),

                // --- FINANCEIRO ---
                Section::make('Resumo Financeiro')
                    ->columns(['default' => 1, 'md' => 4]) 
                    ->schema([
                        TextEntry::make('stock.name')
                            ->label('Armazém Destino')
                            ->icon('heroicon-o-cube')
                            ->columnSpanFull(), 

                        TextEntry::make('total_amount') 
                            ->label('Subtotal (Itens)')
                            ->formatStateUsing(fn ($state) => 'Kz ' . number_format($state ?? 0, 2, ',', '.')),

                        TextEntry::make('discount_total')
                            ->label('Desconto')
                            ->color('danger')
                            ->formatStateUsing(fn ($state) => '- Kz ' . number_format($state ?? 0, 2, ',', '.')),

                        TextEntry::make('freight_cost') 
                            ->label('Frete')
                            ->formatStateUsing(fn ($state) => '+ Kz ' . number_format($state ?? 0, 2, ',', '.')),

                        TextEntry::make('iva_amount') 
                            ->label('IVA (14%)')
                            ->formatStateUsing(fn ($state) => '+ Kz ' . number_format($state ?? 0, 2, ',', '.')),

                        TextEntry::make('total_payable') 
                            ->label('TOTAL FINAL')
                            ->formatStateUsing(fn ($state) => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->color('success')
                            ->weight('bold')
                            ->size('xl')
                            ->columnSpanFull(),
                            
                        TextEntry::make('internal_notes')
                            ->label('Observações Internas')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),
                
                Section::make('Dados do Fornecedor')
                    ->columns(['default' => 1, 'md' => 3])
                    ->collapsed()
                    ->schema([
                        TextEntry::make('supplier.name')->label('Empresa')->weight('bold'),
                        TextEntry::make('supplier.nif')->label('NIF'),
                        TextEntry::make('supplier.email')->label('Email')->icon('heroicon-o-envelope'),
                        TextEntry::make('supplier.phone')->label('Telefone')->icon('heroicon-o-phone'),
                        TextEntry::make('supplier.address')->label('Endereço')->columnSpanFull(),
                    ]),
            ]);
    }

    public function getRelations(): array
    {
        return [
            RelationManagers\PurchaseItemsRelationManager::class,
            RelationManagers\PurchaseAttachmentsRelationManager::class,
        ];
    }
}