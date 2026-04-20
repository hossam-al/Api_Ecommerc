<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'shipping_cost',
        'discount_amount',
        'coupon_code',
        'address_title',
        'address_details',
        'governorate_name',
        'status',
        'notes',
    ];

    /* ================= Relations ================= */

    // صاحب الطلب
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // عناصر الطلب (المنتجات)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // (اختياري) جلب المنتجات مباشرة
    public function product()
    {
        return $this->belongsToMany(products::class, 'order_items')
            ->withPivot('variant_id', 'quantity', 'price', 'subtotal')
            ->withTimestamps();
    }
}
