<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\Product; 
use Illuminate\Support\HtmlString; 
use Illuminate\Support\Facades\DB; 
// ❌ REMOVIDO: use Filament\Infolists\Components\Placeholder;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

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
                // === SECÇÃO 1: INFORMAÇÕES ESSENCIAIS ===
                Infolists\Components\Section::make('Informações Essenciais e Preço')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('Imagem Principal')
                            ->height('250px')
                            ->columnSpan(1),
                        
                        Infolists\Components\Group::make()
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nome')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('sku')
                                    ->label('SKU (Código)')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),
                                    
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Preço Normal')
                                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color('gray'), 
                                
                                Infolists\Components\TextEntry::make('promotional_price')
                                    ->label('Preço Promocional')
                                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color('success')
                                    ->weight('bold')
                                    ->visible(fn ($state) => $state > 0), 
                                    
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('Unidade de Venda'),

                                Infolists\Components\TextEntry::make('categories.name')
                                    ->label('Categorias')
                                    ->badge()
                                    ->color('primary')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // === SECÇÃO DE RASTREIO DE ESTOQUE ===
                Infolists\Components\Section::make('Rastreio de Estoque')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        // ✅ CORREÇÃO: Trocado Placeholder por TextEntry
                        Infolists\Components\TextEntry::make('stock_tracking')
                            ->label('') 
                            ->state('stock_data') // Adiciona um estado "dummy"
                            ->formatStateUsing(function (Product $record): HtmlString { // Usa formatStateUsing
                                
                                $stocks = DB::table('stock_movements')
                                    ->join('stocks', 'stock_movements.stock_id', '=', 'stocks.id')
                                    ->where('stock_movements.product_id', $record->id)
                                    ->groupBy('stock_movements.stock_id', 'stocks.name', 'stocks.location')
                                    ->select('stocks.name', 'stocks.location', DB::raw('SUM(stock_movements.quantity) as total_quantity'))
                                    ->get();

                                if ($stocks->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">Este produto não tem movimentos de estoque registados.</p>');
                                }

                                $html = '<table style="width: 100%; border-collapse: collapse;">';
                                $html .= '<thead><tr style="text-align: left; border-bottom: 1px solid #ddd;">';
                                $html .= '<th style="padding: 8px;">Armazém</th>';
                                $html .= '<th style="padding: 8px;">Localização</th>';
                                $html .= '<th style="padding: 8px; text-align: right;">Qtd. Atual</th>';
                                $html .= '</tr></thead><tbody>';

                                $totalGlobal = 0;
                                foreach ($stocks as $stock) {
                                    $totalGlobal += $stock->total_quantity;
                                    $color = $stock->total_quantity > 0 ? 'green' : 'red';
                                    
                                    $html .= '<tr style="border-bottom: 1px solid #eee;">';
                                    $html .= '<td style="padding: 8px; font-weight: 500;">' . e($stock->name) . '</td>';
                                    $html .= '<td style="padding: 8px; font-size: 0.875rem; color: #666;">' . e($stock->location ?? 'N/A') . '</td>';
                                    $html .= '<td style="padding: 8px; text-align: right; font-weight: bold; color: ' . $color . ';">';
                                    $html .= number_format($stock->total_quantity, 0, ',', '.') . ' ' . e($record->unit);
                                    $html .= '</td></tr>';
                                }
                                
                                // ✅ CORREÇÃO: Removido o 'background-color' fixo para funcionar no Dark Mode
                                $html .= '<tr>'; 
                                $html .= '<td colspan="2" style="padding: 8px; text-align: right; font-weight: bold;">Estoque Total (Global):</td>';
                                $html .= '<td style="padding: 8px; text-align: right; font-weight: bold; font-size: 1.1rem;">';
                                $html .= number_format($totalGlobal, 0, ',', '.') . ' ' . e($record->unit);
                                $html .= '</td></tr>';

                                $html .= '</tbody></table>';
                                return new HtmlString($html);
                            }),
                    ]),
                
                // === SECÇÃO 3: GALERIA DE IMAGENS ===
                Infolists\Components\Section::make('Galeria de Imagens')
                    ->visible(fn (Product $record) => $record->images->isNotEmpty())
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('images')
                            ->label('')->schema([
                                Infolists\Components\ImageEntry::make('url')
                                    ->label('')->height('150px')->width('auto')
                            ])->grid(6)->columnSpanFull(),
                    ]),
                
                // === SECÇÃO 4: DESCRIÇÕES E VARIAÇÕES ===
                Infolists\Components\Section::make('Descrições e Variações')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('short_description')
                            ->label('Descrição Curta (WhatsApp)')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Medium)
                            ->weight('semibold')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('description')
                            ->label('Descrição Detalhada')
                            ->markdown() 
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('colors')->label('Cores')->badge()->separator(','),
                        Infolists\Components\TextEntry::make('sizes')->label('Tamanhos')->badge()->separator(','),
                    ]),
                    
                // === SECÇÃO 5: ATRIBUTOS FÍSICOS ===
                Infolists\Components\Section::make('Atributos Físicos e Logística')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('drawing')->label('Desenho/Código'),
                        Infolists\Components\TextEntry::make('weight')->label('Peso')->suffix(' kg'),
                        Infolists\Components\TextEntry::make('volume')->label('Volume'),
                        Infolists\Components\TextEntry::make('dimension')->label('Dimensões'),
                    ]),
            ]);
    }
}

