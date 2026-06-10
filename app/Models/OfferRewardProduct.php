<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferRewardProduct extends Model
{
    protected $fillable = [
        'offer_id',
        'product_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
