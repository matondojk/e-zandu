<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // ✅ UUID

class Coupon extends Model
{
    use HasFactory, HasUuids; // ✅ UUID

    protected $fillable = [
        'code',
        'type',
        'value',
        'usage_limit',
        'expires_at',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relação: Um cupão pode ser usado em muitos carrinhos.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}