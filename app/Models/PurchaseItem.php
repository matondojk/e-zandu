<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PurchaseItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = "purchase_items";

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_cost',
        'discount',
        'iva_amount',
        'notes',
        'unit',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'discount' => 'float',
        'iva_amount' => 'float',
        'quantity' => 'integer',
    ];

    /**
     * Accessor para o 'total_cost' (Custo Total ANTES de impostos)
     * (quantity * unit_cost) - discount
     */
    protected function getTotalCostAttribute(): float
    {
        return ((float)$this->unit_cost * (int)$this->quantity) - (float)$this->discount;
    }

    /**
     * ✅ NOVO: Accessor para o 'total_payable' (Custo Total + IVA do item)
     */
    protected function getTotalPayableAttribute(): float
    {
        // Retorna o Custo Total (Subtotal) + o IVA do item
        return $this->total_cost + (float)$this->iva_amount;
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
