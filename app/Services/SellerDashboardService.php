<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Order;
use App\Models\User;
use App\Models\products;
use App\Support\ApiResponseBuilder;

class SellerDashboardService
{
    public function __construct(
        protected ProductViewService $productViewService,
        protected OrderService $orderService
    ) {
    }

    public function home(?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller dashboard analytics', 403);
        }

        $sellerProducts = products::query()->ownedBy($seller->id);
        $now = now();

        $latestProducts = products::query()
            ->with(['category', 'brand', 'images', 'variants.images', 'user:id,name,email,phone'])
            ->ownedBy($seller->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn(products $product) => (new ProductResource($product))->resolve())
            ->values()
            ->all();

        return ApiResponseBuilder::success('Seller dashboard analytics retrieved successfully', [
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'seller_status' => $seller->resolveSellerStatus(),
            ],
            'overview' => [
                'total_products' => (clone $sellerProducts)->count(),
                'published_products' => (clone $sellerProducts)->published()->count(),
                'pending_products' => (clone $sellerProducts)->pendingReview()->count(),
                'rejected_products' => (clone $sellerProducts)->rejected()->count(),
                'total_orders' => Order::query()
                    ->whereHas('items.product', fn($query) => $query->ownedBy($seller->id))
                    ->count(),
            ],
            'product_views' => [
                'today' => $this->productViewService->countSellerViewsSince($seller->id, $now->copy()->startOfDay(), $now),
                'this_week' => $this->productViewService->countSellerViewsSince($seller->id, $now->copy()->startOfWeek(), $now),
                'this_month' => $this->productViewService->countSellerViewsSince($seller->id, $now->copy()->startOfMonth(), $now),
            ],
            'latest_products' => $latestProducts,
            'latest_orders' => $this->orderService->latestSellerOrders($seller->id, 5),
            'generated_at' => $now->toDateTimeString(),
        ]);
    }

    public function accountStatus(?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller account status', 403);
        }

        $seller->loadMissing('role:id,name,slug');
        $status = $seller->resolveSellerStatus();

        return ApiResponseBuilder::success('Seller account status retrieved successfully', [
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'phone' => $seller->phone,
                'role' => $seller->role ? [
                    'id' => $seller->role->id,
                    'name' => $seller->role->name,
                    'slug' => $seller->role->slug,
                ] : null,
            ],
            'account_status' => [
                'status' => $status,
                'seller_status' => $status,
                'is_active' => $status === 'approved' && !$seller->is_banned && $seller->hasVerifiedEmail(),
                'is_email_verified' => $seller->hasVerifiedEmail(),
                'is_under_review' => $status === 'pending_review',
                'is_approved' => $status === 'approved',
                'is_rejected' => $status === 'rejected',
                'is_banned' => $status === 'banned' || (bool) $seller->is_banned,
                'email_verified_at' => $seller->email_verified_at?->toDateTimeString(),
                'created_at' => $seller->created_at?->toDateTimeString(),
                'updated_at' => $seller->updated_at?->toDateTimeString(),
            ],
        ]);
    }

    protected function isSeller(?User $user): bool
    {
        return $user && (int) $user->role_id === 2;
    }
}
