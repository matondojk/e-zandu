<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionHistory extends Model
{
    use HasFactory, HasUuids; // <-- USAR UUIDs

    protected $table = 'subscription_histories';

    protected $fillable = [
        'event',
        'description',
        'price'
    ];
}