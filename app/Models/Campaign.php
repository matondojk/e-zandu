<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'channel', 'scheduled_at', 'status', 'content_type', 
        'coupon_id', 'whatsapp_template', 'email_subject', 
        'email_template', 'audience_type', 'audience_prompt',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // protected $attributes = [
    //     'status' => 'scheduled',
    // ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function audience(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_audience')
            ->withPivot('status', 'error_message', 'sent_at');
    }

    public function productsInPromotion(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_product_promotion')
            ->withPivot('promotional_price');
    }
}