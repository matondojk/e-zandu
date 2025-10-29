<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductImage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'url',
        'sort_order',
    ];


    protected function url(): Attribute
    {
        return Attribute::make(
            // Accessor (BD -> Formulário Filament)
            get: function (string $value) {
                // Se o valor for um URL completo (o que o Observer salva)
                if (Str::startsWith($value, ['http://', 'https://'])) {
                    // Retorna o caminho relativo (ex: products/gallery/hash.jpg)
                    return Str::after(parse_url($value, PHP_URL_PATH) ?? '', '/storage/');
                }
                // Caso contrário (se já for um path ou nulo), retorna o valor
                return $value;
            },
            // Mutator (Filament -> BD) - O Observer já faz a conversão de Path->URL, mas deixamos aqui por segurança
            // set: function (string $value) { return $value; } 
        );
    }
    /**
     * Relação N para 1: A imagem pertence a um produto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
