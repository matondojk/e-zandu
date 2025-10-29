<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Stock extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'location',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relação 1 para N: Um estoque tem muitas movimentações.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Relação 1 para N: Um estoque pode receber muitas compras.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
