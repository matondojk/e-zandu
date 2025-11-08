<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importar
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    use HasFactory, HasUuids; // Usar HasUuids

    protected $fillable = [
        'version',
        'title',
        'features',
        'status',
    ];

    protected $casts = [
        'features' => 'array', // Converte o JSON para array
    ];
}