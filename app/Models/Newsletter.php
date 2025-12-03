<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email',
        'is_subscribed',
    ];

    protected $casts = [
        'is_subscribed' => 'boolean',
    ];
}