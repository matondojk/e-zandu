<?php

namespace App\Observers;

use App\Models\ProductImage;

class ProductImageObserver
{
    /**
     * NÃO FAZ NADA.
     * Deixamos o Filament salvar o caminho relativo nativamente.
     */
    public function saving(ProductImage $productImage): void
    {
        // Lógica removida para permitir salvar apenas o caminho relativo (path).
    }
}