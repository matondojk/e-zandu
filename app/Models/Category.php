<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Importar Str
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasFactory, HasSlug, HasUuids;

    protected $fillable = ['name', 'slug', 'description', 'icon'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(self::$logAttributes)
    //         ->setDescriptionForEvent(fn(string $eventName) => "Categoria {$this->name} foi {$eventName}.")
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs();
    // }

    /**
     * Relação: Uma Categoria tem muitos Produtos.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
