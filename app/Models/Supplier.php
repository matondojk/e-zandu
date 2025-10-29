<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Supplier extends Model
{
    use HasFactory,HasUuids;

    protected $fillable = [
        'name', 'contact_name', 'phone', 'email', 'nif', 'address',
    ];

    /**
     * Relação 1 para N: Um fornecedor pode ter muitas compras.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}