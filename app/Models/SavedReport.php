<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Se usar UUIDs
use App\Models\User;

class SavedReport extends Model
{
    use HasFactory,HasUuids; //, HasUuids; // Descomente HasUuids se a sua tabela os usar

    // ✅ ATUALIZADO: Adicionar novos campos
    protected $fillable = [
        'user_id',
        'name',
        'prompt',
        'is_scheduled',
        'frequency',
        'recipient_email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}