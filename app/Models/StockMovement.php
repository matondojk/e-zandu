<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockMovement extends Model
{
    use HasFactory,HasUuids;
    
    // Define a constante para o tipo de movimento de compra
    public const TYPE_IN_PURCHASE = 'in_purchase';

    protected $table='stock_movements';
    
    // Se existir um tipo de venda (que virá depois)
    // public const TYPE_OUT_SALE = 'out_sale'; 

    protected $fillable = [
        'stock_id', 
        'product_id', 
        'type', 
        'quantity', 
        'unit_cost', 
        'reference_type', 
        'reference_id', 
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'float',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function reference(): BelongsTo
    {
        return $this->belongsTo($this->reference_type, 'reference_id');
    }
}