<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Usar Authenticatable para Login
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'delivery_address', // <-- NOVO: Endereço de entrega
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Relação: Um Cliente pode ter muitos Carrinhos/Pedidos.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
