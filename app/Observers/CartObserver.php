<?php

namespace App\Observers;

use App\Models\Cart;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Notifications\Notification;

class CartObserver
{
    /**
     * Lidar com o evento "updating" (antes de salvar).
     */
    public function updating(Cart $cart): bool
    {
        // Verificar se o status está a ser mudado DE 'active' PARA 'paid'
        if ($cart->isDirty('status') && $cart->getOriginal('status') === 'active' && $cart->status === 'paid') {
            
            $defaultStock = Stock::where('is_default', true)->first();
            if (!$defaultStock) {
                Notification::make()->title('Erro Crítico de Configuração')
                    ->body('Nenhum "Armazém Padrão" definido. Ação cancelada.')
                    ->danger()->send();
                return false; // Cancela o salvamento
            }

            $stockErrors = [];
            $cart->load('items.product'); // Garantir que os itens estão carregados
            
            foreach ($cart->items as $item) {
                // ✅ ESTA É A LINHA QUE DAVA ERRO. AGORA VAI FUNCIONAR.
                $currentStock = $item->product->getStockIn($defaultStock->id); 
                
                if ($currentStock < $item->quantity) {
                    $stockErrors[] = "Produto '{$item->product->name}': Pedido (Qtd: {$item->quantity}), Estoque (Qtd: {$currentStock})";
                }
            }

            if (!empty($stockErrors)) {
                $errorBody = "Não há estoque suficiente no Armazém Padrão ('{$defaultStock->name}') para os seguintes itens:\n" . implode("\n", $stockErrors);
                Notification::make()->title('Falha na Verificação de Estoque!')
                    ->body($errorBody)->danger()->duration(10000)->send();
                return false; // Cancela o salvamento
            }
        }
        
        return true; // Permite o salvamento
    }

    /**
     * Lidar com o evento "updated" (depois de salvar).
     */
    public function updated(Cart $cart): void
    {
        if ($cart->wasChanged('status') && $cart->getOriginal('status') === 'active' && $cart->status === 'paid') {
            
            $defaultStock = Stock::where('is_default', true)->first();
            
            foreach ($cart->items as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'stock_id' => $defaultStock->id,
                    'quantity' => -$item->quantity, // Saída
                    'type' => 'sale',
                    'reference' => "Pedido #" . $cart->id,
                ]);
            }
        }
    }
}