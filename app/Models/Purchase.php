<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Purchase extends Model
{
    use HasFactory, HasUuids;

    /**
     * ✅ CORREÇÃO CRÍTICA:
     * Adicionar todos os campos que o Trigger atualiza.
     */
    protected $fillable = [
        'supplier_id',
        'stock_id',
        'document_date',
        'document_number',
        'is_delivered',
        'discount_total', // Essencial
        'total_amount',   // Essencial
        'freight_cost',   // Essencial
        'iva_amount',     // Essencial
        'total_payable',  // Essencial
    ];

    // /**
    //  * ✅ CORREÇÃO CRÍTICA:
    //  * Definir os 'casts' para que o Filament saiba que são números.
    //  */
    // protected $casts = [
    //     'document_date' => 'date',
    //     'is_delivered' => 'boolean',
    //     'discount_total' => 'float',
    //     'total_amount' => 'float',
    //     'freight_cost' => 'float',
    //     'iva_amount' => 'float',
    //     'total_payable' => 'float',
    // ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
