<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'user_id',
        'rating',
        'comment',
        'status',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(product_variants::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
