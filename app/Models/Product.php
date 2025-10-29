<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasFactory, HasSlug, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'price',
        'image_url',
        'slug',
        'sku',
        'unit',
        'drawing',
        'weight',
        'volume',
        'dimension',
        'colors',
        'sizes',
        'promotional_price'
    ];

    protected $casts = [
        'price' => 'float',
        'colors' => 'array',
        'sizes' => 'array',
        'promotional_price' => 'float',
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function crossSellProducts()
    {
        return $this->belongsToMany(
            Product::class,
            'product_cross_sell',
            'main_product_id',
            'recommended_product_id'
        );
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * ✅ CORREÇÃO:
     * O ID do Stock (Armazém) é um UUID, que é uma STRING, não um INT.
     * Trocamos o type-hint de 'int' para 'string'.
     */
    public function getStockIn(string $stockId) // <-- CORRIGIDO DE 'int' PARA 'string'
    {
        $totalStock = $this->movements()
            ->where('stock_id', $stockId) // stock_id é foreignUuid (string), $stockId é uuid (string)
            ->sum('quantity');

        return max(0, $totalStock);
    }
}

