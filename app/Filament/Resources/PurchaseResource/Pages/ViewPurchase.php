<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
// use Illuminate\Support\Number; // ✅ REMOVIDO
use App\Filament\Resources\PurchaseResource\RelationManagers; 

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detalhes da Compra')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('document_date')
                            ->label('Data da Fatura/Doc.')
                            ->date('d/m/Y')
                            ->size('lg'), 
                        TextEntry::make('document_number')
                            ->label('Nº Documento')
                            ->size('lg') 
                            ->weight('bold'),
                        TextEntry::make('is_delivered')
                            ->label('Estado da Entrega')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Entregue' : 'Pendente')
                            ->color(fn (bool $state) => $state ? 'success' : 'warning'),
                    ]),

                // ✅ SEÇÃO FINANCEIRA ATUALIZADA COM number_format
                Section::make('Resumo Financeiro e Armazém')
                    ->columns(4) // Aumentado para 4 colunas
                    ->schema([
                        TextEntry::make('stock.name')
                            ->label('Armazém Destino')
                            ->icon('heroicon-o-cube')
                            ->size('lg')
                            ->columnSpan(4), // Ocupa a linha toda

                        TextEntry::make('total_amount') // Este é o Subtotal
                            ->label('Subtotal (Itens)')
                            // ✅ CORREÇÃO: Substituído Number::currency por number_format
                            ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->icon('heroicon-o-archive-box')
                            ->weight('medium'),

                        TextEntry::make('discount_total')
                            ->label('Desconto Total (Itens)')
                            // ✅ CORREÇÃO: Substituído Number::currency por number_format
                            ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->color('danger')
                            ->icon('heroicon-o-minus'),

                        TextEntry::make('freight_cost') // NOVO: Frete
                            ->label('Custo do Frete')
                            // ✅ CORREÇÃO: Substituído Number::currency por number_format
                            ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->icon('heroicon-o-truck'),

                        TextEntry::make('iva_amount') // NOVO: IVA
                            ->label('IVA (14%)')
                            // ✅ CORREÇÃO: Substituído Number::currency por number_format
                            ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->icon('heroicon-o-receipt-percent'),

                        TextEntry::make('total_payable') // NOVO: Total Final
                            ->label('Total Final (c/ IVA e Frete)')
                            // ✅ CORREÇÃO: Substituído Number::currency por number_format
                            ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state ?? 0, 2, ',', '.'))
                            ->color('success')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-currency-dollar')
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Informações do Fornecedor')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('supplier.name')
                            ->label('Nome da Empresa')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('supplier.contact_name')
                            ->label('Pessoa de Contacto'),
                        TextEntry::make('supplier.nif')
                            ->label('NIF/Contribuinte'),
                        TextEntry::make('supplier.phone')
                            ->label('Telefone/WhatsApp')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('supplier.email')
                            ->label('E-mail')
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('supplier.address')
                            ->label('Endereço')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Registar os Relation Managers para a página de View.
     * (Isto garante que a tabela "Itens da Compra" aparece)
     */
    public function getRelations(): array
    {
        return [
            RelationManagers\PurchaseItemsRelationManager::class,
        ];
    }
}

