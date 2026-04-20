<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class products_image extends Model
{
    protected $table = 'products_images';

    protected $fillable = [
        'product_id',
        'variant_id',
        'url',
        'path',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(product_variants::class, 'variant_id');
    }
}
