<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importar o Trait de UUID
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDetail extends Model
{
    use HasFactory, HasUuids; // Usar o Trait

    /**
     * A tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'subscription_details';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'central_subscription_id',
        'plan_identifier',
        'plan_name',
        'status',
        'ends_at',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Converte a coluna 'ends_at' para um objeto Carbon (para datas)
        'ends_at' => 'datetime', 
    ];
}