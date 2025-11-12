<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'expected_delivery_date' => 'date',
        // 'attachments' => 'array',
        'is_delivered' => 'boolean',
        'calculate_iva' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $purchase->purchase_number = 'PO-' . strtoupper(Str::random(8));
            }
        });
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
