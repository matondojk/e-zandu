<?php

namespace App\Observers;

use App\Models\Stock;

class StockObserver
{
    /**
     * Lidar com o evento "saving" (antes de criar ou atualizar).
     *
     * @param  \App\Models\Stock  $stock
     * @return void
     */
    public function saving(Stock $stock): void
    {
        // Verifica se o campo 'is_default' está sendo definido como 'true'
        // E se este valor realmente mudou (isDirty)
        if ($stock->is_default && $stock->isDirty('is_default')) {
            
            // Se sim, atualiza TODOS os outros registos para 'is_default' = false
            Stock::where('id', '!=', $stock->id) // Onde o ID é DIFERENTE do atual
                 ->where('is_default', true)    // E que sejam 'default'
                 ->update(['is_default' => false]);
        }
    }
}