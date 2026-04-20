<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponUser extends Model
{
    use HasFactory;

    protected $table = 'coupon_user';

    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /* ================= Relations ================= */

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
