<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'url', // Aqui será salvo apenas: 'products/gallery/arquivo.jpg'
        'sort_order',
    ];

    /**
     * Apenas um helper para quando você precisar exibir no Frontend/API.
     * Uso: $productImage->full_url
     */
    public function getFullUrlAttribute()
    {
        return $this->url ? Storage::disk('public')->url($this->url) : null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}