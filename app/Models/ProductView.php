<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
