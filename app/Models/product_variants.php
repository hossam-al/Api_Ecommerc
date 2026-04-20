<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product_variants extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'size',
        'price',
        'stock',
        'sku',
        'primary_image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(products::class);
    }

    public function images()
    {
        return $this->hasMany(products_image::class, 'variant_id');
    }
}
