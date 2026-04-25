<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'image_url',
        'is_active',
        'user_id',
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    public function products()
    {
        return $this->hasMany(products::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
