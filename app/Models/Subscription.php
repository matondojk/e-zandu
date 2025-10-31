<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Importar Str
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
    use HasFactory;

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
}
