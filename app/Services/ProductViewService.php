<?php

namespace App\Services;

use App\Models\ProductView;
use App\Models\User;
use App\Models\products;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class ProductViewService
{
    public function recordView(products $product, ?User $viewer, Request $request): void
    {
        if (!$product->is_active) {
            return;
        }

        if ($viewer && (int) $product->user_id === (int) $viewer->id) {
            return;
        }

        if ($viewer && (int) $viewer->role_id === 1) {
            return;
        }

        ProductView::create([
            'product_id' => $product->id,
            'user_id' => $viewer?->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1024),
            'viewed_at' => now(),
        ]);
    }

    public function countSellerViewsSince(int $sellerId, CarbonInterface $from, ?CarbonInterface $to = null): int
    {
        $query = ProductView::query()
            ->join('products', 'products.id', '=', 'product_views.product_id')
            ->where('products.user_id', $sellerId)
            ->where('product_views.viewed_at', '>=', $from);

        if ($to) {
            $query->where('product_views.viewed_at', '<=', $to);
        }

        return (int) $query->count();
    }
}
