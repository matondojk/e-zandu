<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model
{
    use HasFactory, HasUuids; // Habilita IDs UUID

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
    ];

    /**
     * Relação Muitos-para-Muitos com Produtos.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'brand_product');
    }
}