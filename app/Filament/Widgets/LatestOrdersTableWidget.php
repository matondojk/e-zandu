<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Cart;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class LatestOrdersTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Pedidos em Trânsito/Entregues'; // Título atualizado
    protected static ?int $sort = 2;

    // Props e Listener para o filtro
    public ?string $startDate = null;
    public ?string $endDate = null;
    protected $listeners = ['dateRangeUpdated' => 'updateDateRange'];

    public function updateDateRange($data): void
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        $this->dispatch('$refresh');
    }

    protected function getTableQuery(): Builder
    {
        $query = Cart::query()
            // Mantém os status de pagamento
            ->whereIn('status', ['paid', 'completed']) 
            
            // ✅ CORREÇÃO: Filtrar como pedido (diferente de 'pending')
            ->where('delivery_status', '!=', 'pending') 
            
            ->orderByDesc('created_at');
        
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        
        return $query->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('Pedido #')
                ->sortable()
                ->url(fn (Cart $record): string => OrderResource::getUrl('view', ['record' => $record])),
                
            Tables\Columns\TextColumn::make('user.name')
                ->label('Cliente')
                ->searchable(),

            // ✅ NOVA COLUNA: Para vermos o status da entrega
            Tables\Columns\TextColumn::make('delivery_status')
                ->label('Entrega')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'shipped' => 'info',
                    'delivered' => 'success',
                    'failed' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ucfirst($state)), // Ex: 'Shipped'

            Tables\Columns\TextColumn::make('status')
                ->label('Pagamento') // Renomeado para clareza
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'completed' => 'success',
                    'paid' => 'info',
                    'active' => 'warning',
                    'canceled' => 'danger',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('total_price')
                ->label('Total')
                ->formatStateUsing(fn ($state): string => Number::currency($state, 'Kz', 'pt_AO'))
                ->alignEnd(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}