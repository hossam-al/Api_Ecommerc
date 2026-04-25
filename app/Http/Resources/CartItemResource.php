<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->variant;
        $product = $this->product;
        $variantImages = $variant?->relationLoaded('images')
            ? $variant->images
            : collect();
        $productImages = $product?->relationLoaded('images')
            ? $product->images
            : collect();

        $dedicatedGalleryImages = $variantImages
            ->map(fn($image) => [
                'id' => $image->id,
                'url' => $image->url,
                'variant_id' => $image->variant_id,
            ])
            ->values()
            ->all();

        $legacyGalleryImages = $productImages
            ->map(fn($image) => [
                'id' => $image->id,
                'url' => $image->url,
                'variant_id' => $image->variant_id,
            ])
            ->values()
            ->all();

        $galleryImages = !empty($dedicatedGalleryImages)
            ? $dedicatedGalleryImages
            : $legacyGalleryImages;

        $primaryImage = $variant?->primary_image
            ?: ($dedicatedGalleryImages[0]['url'] ?? null)
            ?: ($legacyGalleryImages[0]['url'] ?? null)
            ?: $product?->primary_image;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'product' => $product ? [
                'id' => $product->id,
                'name_en' => $product->name_en,
                'name_ar' => $product->name_ar,
                'primary_image' => $product->primary_image,
                'is_active' => (bool) $product->is_active,
            ] : null,
            'variant' => $variant ? [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'color' => $variant->color,
                'size' => $variant->size,
                'price' => $variant->price,
                'stock' => $variant->stock,
                'primary_image' => $primaryImage,
                'gallery_images' => $galleryImages,
                'dedicated_gallery_images' => $dedicatedGalleryImages,
                'image_source' => !empty($dedicatedGalleryImages)
                    ? 'variant'
                    : (!empty($legacyGalleryImages) ? 'product_legacy_fallback' : 'product_cover_fallback'),
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
