<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Usar Authenticatable para Login
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * Define explicitamente a tabela 'users'
     */
    protected $table = 'users';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'delivery_address',
        'role',
    ];

    /**
     * Define valores padrão
     */
    protected $attributes = [
        'role' => 'customer',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relação: Um Cliente pode ter muitos Carrinhos/Pedidos.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
