<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MauticConfig extends Model
{
    use HasUuids;

    protected $table = 'mautic_configs';
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'integrar',
        'tracking_code',
        'username',
        'password',
        'mautic_url',
    ];
}
