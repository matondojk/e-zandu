<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importação necessária para BelongsTo

class Cart extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * Define os casts para os novos campos de data/hora
     * para que sejam tratados como instâncias Carbon.
     */
    protected $casts = [
        'payment_confirmed_at' => 'datetime',
        'delivery_confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($cart) {
            if (empty($cart->cart_number)) {
                $cart->cart_number = 'CRT-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    
    // --- NOVOS RELACIONAMENTOS DE AUDITORIA ---

    /**
     * Usuário que confirmou o pagamento.
     */
    public function paymentConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by');
    }
    
    /**
     * Usuário que confirmou a entrega.
     */
    public function deliveryConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_confirmed_by');
    }

    /**
     * Usuário que rejeitou o pedido.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}