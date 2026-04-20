<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class brands extends Model
{
    use HasFactory;

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }


    protected $fillable = [
        'name_en',
        'name_ar',
        'logo',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(products::class);
    }
}
