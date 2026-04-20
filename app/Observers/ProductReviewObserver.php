<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Services\ProductReviewSummaryService;

class ProductReviewObserver
{
    public function __construct(
        protected ProductReviewSummaryService $summaryService
    ) {
    }

    public function saved(ProductReview $review): void
    {
        $this->summaryService->syncForProductId((int) $review->product_id);
    }

    public function deleted(ProductReview $review): void
    {
        $this->summaryService->syncForProductId((int) $review->product_id);
    }
}
