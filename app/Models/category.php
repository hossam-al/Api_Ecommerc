<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class category extends Model
{
    use HasFactory;

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    protected $table = "categories";
    protected $fillable = ['name_en', 'name_ar', 'description_en', 'description_ar', 'image_url', 'is_active', 'user_id'];

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
