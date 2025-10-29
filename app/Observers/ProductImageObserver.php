<?php

namespace App\Observers;

use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductImageObserver
{
    /**
     * Manipula o evento "saving" (antes de criar OU atualizar).
     * Garante que o URL completo seja salvo na coluna 'url'.
     */
    public function saving(ProductImage $productImage): void
    {
        $url = $productImage->url;
        
        // Verifica se o valor é um caminho relativo (não contém http/https)
        if (is_string($url) && !Str::startsWith($url, ['http://', 'https://'])) {
            // Converte o caminho relativo do disco 'public' para o URL completo
            $productImage->url = Storage::disk('public')->url($url);
        }
        
        // Se já for um URL completo (durante uma atualização sem novo upload), o valor é mantido.
    }
}