<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class products extends Model
{
    protected $fillable = [
        'user_id',
        'brand_id',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'primary_image',
        'is_active',
        'review_status',
        'rejection_reason',
        'is_featured',
        'category_id',
        'average_rating',
        'reviews_count',
        'discount_type',
        'discount_value',
        'discount_start_at',
        'discount_end_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'average_rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'discount_value' => 'decimal:2',
        'discount_start_at' => 'datetime',
        'discount_end_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (products $product) {
            if (blank($product->review_status)) {
                $product->review_status = $product->is_active ? 'approved' : 'pending';
            }
        });
    }

    public function images()
    {
        return $this->hasMany(products_image::class, 'product_id')
            ->whereNull('variant_id');
    }

    public function allImages()
    {
        return $this->hasMany(products_image::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->select('id', 'name_en', 'name_ar', 'is_active');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(brands::class);
    }

    public function variants()
    {
        return $this->hasMany(product_variants::class, 'product_id');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('variant_id', 'quantity', 'price', 'subtotal')
            ->withTimestamps();
    }

    public function views()
    {
        return $this->hasMany(ProductView::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('review_status', 'approved');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('review_status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('review_status', 'rejected');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('review_status', 'approved');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->approved()->where('is_active', false);
    }

    public function resolveBasePrice(): float
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->get(['price']);

        $basePrice = $variants->min(fn(product_variants $variant) => (float) $variant->price);

        return round((float) ($basePrice ?? 0), 2);
    }

    public function hasActiveDiscount(?Carbon $at = null): bool
    {
        $at ??= now();

        if (blank($this->discount_type) || $this->discount_value === null) {
            return false;
        }

        if ((float) $this->discount_value <= 0) {
            return false;
        }

        if ($this->discount_start_at && $at->lt($this->discount_start_at)) {
            return false;
        }

        if ($this->discount_end_at && $at->gt($this->discount_end_at)) {
            return false;
        }

        return true;
    }

    public function resolveDiscountAmount(?float $price = null, ?Carbon $at = null): float
    {
        $price ??= $this->resolveBasePrice();

        if (!$this->hasActiveDiscount($at) || $price <= 0) {
            return 0.0;
        }

        $discountValue = (float) $this->discount_value;
        $discountAmount = $this->discount_type === 'percentage'
            ? ($price * $discountValue) / 100
            : $discountValue;

        return round(min($discountAmount, $price), 2);
    }

    public function resolveFinalPrice(?float $price = null, ?Carbon $at = null): float
    {
        $price ??= $this->resolveBasePrice();

        return round(max($price - $this->resolveDiscountAmount($price, $at), 0), 2);
    }
}
