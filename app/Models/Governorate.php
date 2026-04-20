<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Governorate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shipping_cost',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
    ];

    /* ================= Relations ================= */

    // المحافظة ليها عناوين كتير
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
