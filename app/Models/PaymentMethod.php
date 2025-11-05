<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentMethod extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'instructions',
        'bank_account_holder',
        'bank_account_number',
        'bank_iban',
        'phone_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}