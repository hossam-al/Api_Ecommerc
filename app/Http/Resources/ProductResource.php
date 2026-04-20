<?php

namespace App\Http\Resources;

use App\Models\product_variants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->resolveProductStatus();
        $basePrice = $this->resource->resolveBasePrice();
        $discountAmount = $this->resource->resolveDiscountAmount($basePrice);
        $finalPrice = $this->resource->resolveFinalPrice($basePrice);
        $hasDiscount = $this->resource->hasActiveDiscount();
        $legacyGalleryImages = $this->mapImages(
            $this->relationLoaded('images') ? $this->images : collect()
        );

        $variants = $this->relationLoaded('variants')
            ? $this->variants
                ->map(fn(product_variants $variant) => $this->mapVariant($variant, $legacyGalleryImages))
                ->values()
                ->all()
            : [];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'primary_image' => $this->primary_image,
            'cover_image' => $this->primary_image,
            'images' => $legacyGalleryImages,
            'legacy_gallery_images' => $legacyGalleryImages,
            'price' => $basePrice,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value !== null ? (float) $this->discount_value : null,
            'discount_start_at' => $this->discount_start_at?->toDateTimeString(),
            'discount_end_at' => $this->discount_end_at?->toDateTimeString(),
            'has_discount' => $hasDiscount,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'average_rating' => (float) $this->average_rating,
            'reviews_count' => (int) $this->reviews_count,
            'has_variant_specific_images' => collect($variants)->contains(
                fn(array $variant) => $variant['has_dedicated_gallery']
            ),
            'status' => $status,
            'review_status' => $this->review_status,
            'rejection_reason' => $this->rejection_reason,
            'is_pending_review' => $this->review_status === 'pending',
            'is_rejected' => $this->review_status === 'rejected',
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'category' => $this->whenLoaded('category'),
            'brand' => $this->whenLoaded('brand'),
            'user' => $this->whenLoaded('user'),
            'variants' => $variants,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    protected function mapVariant(product_variants $variant, array $legacyGalleryImages): array
    {
        $dedicatedGalleryImages = $this->mapImages(
            $variant->relationLoaded('images') ? $variant->images : collect()
        );
        $variantPrice = (float) $variant->price;
        $discountAmount = $this->resource->resolveDiscountAmount($variantPrice);
        $finalPrice = $this->resource->resolveFinalPrice($variantPrice);

        $galleryImages = !empty($dedicatedGalleryImages)
            ? $dedicatedGalleryImages
            : $legacyGalleryImages;

        $primaryImage = $variant->primary_image
            ?: ($dedicatedGalleryImages[0]['url'] ?? null)
            ?: ($legacyGalleryImages[0]['url'] ?? null)
            ?: $this->primary_image;

        return [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'color' => $variant->color,
            'size' => $variant->size,
            'price' => $variant->price,
            'has_discount' => $this->resource->hasActiveDiscount(),
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'stock' => $variant->stock,
            'sku' => $variant->sku,
            'primary_image' => $primaryImage,
            'images' => $galleryImages,
            'gallery_images' => $galleryImages,
            'dedicated_gallery_images' => $dedicatedGalleryImages,
            'has_dedicated_gallery' => !empty($dedicatedGalleryImages),
            'image_source' => !empty($dedicatedGalleryImages)
                ? 'variant'
                : (!empty($legacyGalleryImages)
                    ? 'product_legacy_fallback'
                    : ($this->primary_image ? 'product_cover_fallback' : 'none')),
            'created_at' => $variant->created_at?->toDateTimeString(),
            'updated_at' => $variant->updated_at?->toDateTimeString(),
        ];
    }

    protected function mapImages(Collection $images): array
    {
        return $images
            ->map(fn($image) => [
                'id' => $image->id,
                'product_id' => $image->product_id,
                'variant_id' => $image->variant_id,
                'url' => $image->url,
                'path' => $image->path,
            ])
            ->values()
            ->all();
    }

    protected function resolveProductStatus(): string
    {
        if ($this->review_status === 'rejected') {
            return 'rejected';
        }

        if ($this->review_status === 'pending') {
            return 'pending';
        }

        return $this->is_active ? 'published' : 'inactive';
    }
}

