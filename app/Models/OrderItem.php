<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'subtotal',
    ];

    /* ================= Relations ================= */

    // كل item تابع لأوردر واحد
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // كل item مرتبط بمنتج
    public function product()
    {
        return $this->belongsTo(products::class);
    }

    public function variant()
    {
        return $this->belongsTo(product_variants::class, 'variant_id');
    }
}
