<?php

namespace App\Services;

use App\Http\Resources\ProductReviewResource;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\products;
use App\Support\ApiResponseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewService
{
    public function index(int|string $productId, array $filters, ?User $user): array
    {
        $product = products::with('user')->find($productId);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $canManageReviews = $this->canManageReviews($user, $product);

        if (!$canManageReviews && (!$product->is_active || $product->review_status !== 'approved')) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $query = ProductReview::query()
            ->with('user:id,name,image_url')
            ->where('product_id', $product->id);

        $statusFilter = $this->normalizeStatusFilter($filters['status'] ?? null);

        if ($canManageReviews) {
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }
        } else {
            $query->where('status', 'approved');
            $statusFilter = 'approved';
        }

        $reviews = $query
            ->orderByDesc('is_verified_purchase')
            ->latest()
            ->get();

        return ApiResponseBuilder::success(
            'Product reviews retrieved successfully',
            ProductReviewResource::collection($reviews)->resolve(),
            200,
            [
                'filters' => [
                    'status' => $statusFilter,
                ],
                'summary' => [
                    'average_rating' => (float) $product->average_rating,
                    'reviews_count' => (int) $product->reviews_count,
                ],
            ]
        );
    }

    public function store(int|string $productId, array $validated, ?User $user): array
    {
        if (!$user) {
            return ApiResponseBuilder::error('Unauthenticated', 401);
        }

        $product = products::find($productId);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $variantId = $this->resolveVariantId($product, $validated['variant_id'] ?? null);

        if (ProductReview::query()->where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'review' => ['You have already reviewed this product.'],
            ]);
        }

        if (!$this->hasVerifiedPurchase($user->id, $product->id, $variantId)) {
            throw ValidationException::withMessages([
                'product_id' => ['Only customers with a completed or delivered purchase can review this product.'],
            ]);
        }

        $review = DB::transaction(function () use ($validated, $product, $variantId, $user) {
            return ProductReview::create([
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => 'pending',
                'is_verified_purchase' => true,
            ]);
        });

        return ApiResponseBuilder::success(
            'Review submitted successfully and is pending moderation',
            (new ProductReviewResource($review->load('user:id,name,image_url')))->resolve(),
            201
        );
    }

    public function update(int|string $productId, array $validated, ?User $user): array
    {
        if (!$user) {
            return ApiResponseBuilder::error('Unauthenticated', 401);
        }

        $product = products::find($productId);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $review = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            return ApiResponseBuilder::error('Review not found', 404);
        }

        $variantId = array_key_exists('variant_id', $validated)
            ? $this->resolveVariantId($product, $validated['variant_id'])
            : $review->variant_id;

        if (!$this->hasVerifiedPurchase($user->id, $product->id, $variantId)) {
            throw ValidationException::withMessages([
                'variant_id' => ['You can only review variants from completed or delivered purchases.'],
            ]);
        }

        $review->update([
            'variant_id' => $variantId,
            'rating' => $validated['rating'] ?? $review->rating,
            'comment' => array_key_exists('comment', $validated) ? $validated['comment'] : $review->comment,
            'status' => 'pending',
            'is_verified_purchase' => true,
        ]);

        return ApiResponseBuilder::success(
            'Review updated successfully and is pending moderation',
            (new ProductReviewResource($review->fresh()->load('user:id,name,image_url')))->resolve()
        );
    }

    public function destroy(int|string $productId, ?User $user): array
    {
        if (!$user) {
            return ApiResponseBuilder::error('Unauthenticated', 401);
        }

        $review = ProductReview::query()
            ->where('product_id', $productId)
            ->where('user_id', $user->id)
            ->first();

        if (!$review) {
            return ApiResponseBuilder::error('Review not found', 404);
        }

        $review->delete();

        return ApiResponseBuilder::success('Review deleted successfully');
    }

    public function moderate(int|string $productId, int|string $reviewId, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can moderate reviews', 403);
        }

        $review = ProductReview::query()
            ->where('id', $reviewId)
            ->where('product_id', $productId)
            ->first();

        if (!$review) {
            return ApiResponseBuilder::error('Review not found', 404);
        }

        $review->update([
            'status' => $validated['status'],
        ]);

        return ApiResponseBuilder::success(
            'Review status updated successfully',
            (new ProductReviewResource($review->fresh()->load('user:id,name,image_url')))->resolve()
        );
    }

    protected function resolveVariantId(products $product, mixed $variantId): ?int
    {
        if (blank($variantId)) {
            return null;
        }

        $variantExists = $product->variants()
            ->whereKey((int) $variantId)
            ->exists();

        if (!$variantExists) {
            throw ValidationException::withMessages([
                'variant_id' => ['The selected variant does not belong to this product.'],
            ]);
        }

        return (int) $variantId;
    }

    protected function hasVerifiedPurchase(int $userId, int $productId, ?int $variantId = null): bool
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->when($variantId, fn(Builder $query) => $query->where('variant_id', $variantId))
            ->whereHas('order', function (Builder $query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereIn('status', ['completed', 'delivered']);
            })
            ->exists();
    }

    protected function normalizeStatusFilter(?string $status): string
    {
        $status = strtolower((string) ($status ?? 'all'));

        return in_array($status, ['pending', 'approved', 'rejected', 'all'], true)
            ? $status
            : 'all';
    }

    protected function canManageReviews(?User $user, products $product): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user && (int) $user->id === (int) $product->user_id;
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
