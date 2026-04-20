<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Governorate;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'governorate_id',
        'title',
        'details',
    ];

    /* ================= Relations ================= */

    // العنوان تابع لمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العنوان تابع لمحافظة
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
