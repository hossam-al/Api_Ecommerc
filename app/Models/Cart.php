<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'subtotal',
    ];

    /* ================= Relations ================= */

    // السلة بتاعة مستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // المنتج الموجود في السلة
    public function product()
    {
        return $this->belongsTo(products::class);
    }

    public function variant()
    {
        return $this->belongsTo(product_variants::class, 'variant_id');
    }
}
