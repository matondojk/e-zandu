<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['product_id', 'user_id', 'rating', 'comment', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        // Retorna o usuário que fez a avaliação
        return $this->belongsTo(User::class);
    }
}
