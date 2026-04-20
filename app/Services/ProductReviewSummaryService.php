<?php

namespace App\Services;

use App\Models\ProductReview;
use App\Models\products;

class ProductReviewSummaryService
{
    public function syncForProductId(int $productId): void
    {
        $summary = ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->selectRaw('COALESCE(AVG(rating), 0) as average_rating, COUNT(*) as reviews_count')
            ->first();

        products::query()
            ->whereKey($productId)
            ->update([
                'average_rating' => round((float) ($summary?->average_rating ?? 0), 2),
                'reviews_count' => (int) ($summary?->reviews_count ?? 0),
            ]);
    }

    public function syncForProduct(products $product): void
    {
        $this->syncForProductId((int) $product->id);
    }
}
