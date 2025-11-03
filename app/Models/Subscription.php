<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'status',
        'plan_name',
        'start_date',
        'end_date',
        'renewed_at',
        'paused_at',
        'canceled_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'renewed_at' => 'datetime',
        'paused_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];
    
    /**
     * Verifica se a assinatura está ativa (não 'paused' nem 'canceled').
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['paused', 'canceled']);
    }
}