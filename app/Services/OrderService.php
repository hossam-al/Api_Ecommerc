<?php

namespace App\Services;

use App\Http\Resources\OrderResource;
use App\Http\Resources\SellerOrderResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\product_variants;
use App\Support\ApiResponseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function index(int $userId): array
    {
        $orders = Order::with(['items.product', 'items.variant'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return ApiResponseBuilder::error('No orders found', 404, ['data' => []]);
        }

        return [
            'status' => true,
            'status_code' => 200,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders)->resolve(),
        ];
    }

    public function show(int|string $id, int $userId): array
    {
        $order = Order::with(['items.product', 'items.variant'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return ApiResponseBuilder::error('Order not found', 404);
        }

        return ApiResponseBuilder::success('Order retrieved successfully', (new OrderResource($order))->resolve());
    }

    public function store(array $validated, User $user): array
    {
        $cartItems = Cart::with(['product', 'variant'])
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return ApiResponseBuilder::error('Cart is empty', 400);
        }

        $address = Address::with('governorate')
            ->where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return ApiResponseBuilder::error('Address not found', 404);
        }

        $subtotal = $cartItems->sum(fn($item) => $item->variant ? $item->variant->price * $item->quantity : 0);
        $shippingCost = $address->governorate->shipping_cost;
        $discount = 0;
        $coupon = null;

        if (!empty($validated['coupon_code'])) {
            $coupon = $this->resolveCoupon($validated['coupon_code'], $subtotal, $user->id);

            if (is_array($coupon)) {
                return $coupon;
            }

            $discount = $this->calculateDiscount($coupon, $subtotal);
        }

        $total = ($subtotal + $shippingCost) - $discount;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Str::upper(Str::random(10)),
                'address_title' => $address->title,
                'address_details' => $address->details,
                'governorate_name' => $address->governorate->name,
                'shipping_cost' => $shippingCost,
                'coupon_code' => $coupon?->code,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                if (!$item->variant) {
                    throw new \RuntimeException('Variant not found for product #' . $item->product_id);
                }

                if ($item->variant->stock < $item->quantity) {
                    throw new \RuntimeException('Insufficient stock for selected variant');
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'subtotal' => $item->variant->price * $item->quantity,
                ]);

                $item->variant->decrement('stock', $item->quantity);
            }

            if ($coupon) {
                CouponUser::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $user->id,
                    'used_at' => now(),
                ]);

                $coupon->increment('used_count');
            }

            Cart::where('user_id', $user->id)->delete();
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponseBuilder::error('Checkout failed', 500, [
                'error' => $exception->getMessage(),
            ]);
        }

        return ApiResponseBuilder::success(
            'Order placed successfully',
            (new OrderResource($order->load(['items.product', 'items.variant'])))->resolve(),
            201,
        );
    }

    public function updateUserOrderNotes(int|string $id, array $validated, int $userId): array
    {
        $order = Order::where('id', $id)->where('user_id', $userId)->first();

        if (!$order) {
            return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
        }

        if ($order->status !== 'pending') {
            return ApiResponseBuilder::error('Only pending orders can be updated', 400, ['status' => '400']);
        }

        $order->update(['notes' => $validated['notes']]);

        return ApiResponseBuilder::success('Order updated successfully', null, 200, [
            'message' => 'Order updated successfully',
            'order' => (new OrderResource($order->fresh()->load(['items.product', 'items.variant', 'user'])))->resolve(),
        ]);
    }

    public function updateAdminOrderNotes(int|string $id, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can update orders', 403);
        }

        $order = Order::find($id);

        if (!$order) {
            return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
        }

        $order->update(['notes' => $validated['notes']]);

        return ApiResponseBuilder::success('Order updated successfully', null, 200, [
            'message' => 'Order updated successfully',
            'order' => (new OrderResource($order->fresh()->load(['items.product', 'items.variant', 'user'])))->resolve(),
        ]);
    }

    public function destroy(int|string $id, int $userId): array
    {
        DB::beginTransaction();

        try {
            $order = Order::with('items')
                ->lockForUpdate()
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$order) {
                DB::rollBack();

                return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
            }

            if ($order->status !== 'pending') {
                DB::rollBack();

                return ApiResponseBuilder::error('Only pending orders can be deleted', 400, ['status' => '400']);
            }

            $this->restoreOrderStock($order);
            $order->delete();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponseBuilder::error('Order deletion failed', 500, [
                'error' => $exception->getMessage(),
            ]);
        }

        return ApiResponseBuilder::success('Order deleted successfully');
    }

    public function updateStatus(int|string $id, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can update order status', 403);
        }

        DB::beginTransaction();

        try {
            $order = Order::with('items')
                ->lockForUpdate()
                ->find($id);

            if (!$order) {
                DB::rollBack();

                return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
            }

            $this->syncOrderStockForStatusChange($order, $validated['status']);
            $order->update(['status' => $validated['status']]);

            DB::commit();
        } catch (\RuntimeException $exception) {
            DB::rollBack();

            return ApiResponseBuilder::error($exception->getMessage(), 400);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponseBuilder::error('Order status update failed', 500, [
                'error' => $exception->getMessage(),
            ]);
        }

        return ApiResponseBuilder::success('Order status updated successfully', null, 200, [
            'message' => 'Order status updated successfully',
            'order' => (new OrderResource($order->fresh()->load(['items.product', 'items.variant', 'user'])))->resolve(),
        ]);
    }

    public function showAllOrders(?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can view all orders', 403);
        }

        $orders = Order::with(['items.product', 'items.variant', 'user'])->latest()->get();

        if ($orders->isEmpty()) {
            return ApiResponseBuilder::error('No orders found', 404, ['data' => []]);
        }

        return [
            'status' => true,
            'status_code' => 200,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders)->resolve(),
        ];
    }

    public function showOrderAdmin(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can view orders', 403);
        }

        $order = Order::with(['items.product', 'items.variant', 'user'])->where('id', $id)->first();

        if (!$order) {
            return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
        }

        return ApiResponseBuilder::success('Order retrieved successfully', (new OrderResource($order))->resolve());
    }

    public function destroyOrder(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can delete orders', 403);
        }

        DB::beginTransaction();

        try {
            $order = Order::with('items')
                ->lockForUpdate()
                ->find($id);

            if (!$order) {
                DB::rollBack();

                return ApiResponseBuilder::error('Order not found', 404, ['status' => '404']);
            }

            if ($this->shouldRestoreStockWhenDeleting($order)) {
                $this->restoreOrderStock($order);
            }

            $order->delete();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponseBuilder::error('Order deletion failed', 500, [
                'error' => $exception->getMessage(),
            ]);
        }

        return ApiResponseBuilder::success('Order deleted successfully');
    }

    public function showSellerOrders(array $filters, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller orders', 403);
        }

        $perPage = max(1, min((int) ($filters['per_page'] ?? 10), 50));
        $paginator = $this->sellerOrdersQuery($seller->id, $filters)->paginate($perPage);

        if ($paginator->isEmpty()) {
            return ApiResponseBuilder::error('No seller orders found', 404);
        }

        return ApiResponseBuilder::success(
            'Seller orders retrieved successfully',
            $this->transformSellerOrderPaginator($paginator, $seller->id),
            200,
            [
                'results' => $paginator->total(),
                'filters' => [
                    'status' => $filters['status'] ?? null,
                    'date_from' => $filters['date_from'] ?? null,
                    'date_to' => $filters['date_to'] ?? null,
                    'sort' => $filters['sort'] ?? 'latest',
                    'per_page' => $perPage,
                ],
            ]
        );
    }

    public function showSellerOrder(int|string $id, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller orders', 403);
        }

        $order = $this->sellerOrdersQuery($seller->id)->where('id', $id)->first();

        if (!$order) {
            return ApiResponseBuilder::error('Order not found', 404);
        }

        return ApiResponseBuilder::success(
            'Seller order retrieved successfully',
            $this->transformSellerOrder($order, $seller->id)
        );
    }

    public function latestSellerOrders(int $sellerId, int $limit = 5): array
    {
        return $this->sellerOrdersQuery($sellerId, ['sort' => 'latest'])
            ->limit(max(1, min($limit, 10)))
            ->get()
            ->map(fn(Order $order) => $this->transformSellerOrder($order, $sellerId))
            ->values()
            ->all();
    }

    protected function sellerOrdersQuery(int $sellerId, array $filters = []): Builder
    {
        $query = Order::with(['user', 'items.product', 'items.variant'])
            ->whereHas('items', function ($query) use ($sellerId) {
                $query->whereHas('product', function ($productQuery) use ($sellerId) {
                    $productQuery->where('user_id', $sellerId);
                });
            });

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sort = $filters['sort'] ?? 'latest';

        return $sort === 'oldest'
            ? $query->orderBy('created_at')->orderBy('id')
            : $query->orderByDesc('created_at')->orderByDesc('id');
    }

    protected function transformSellerOrder(Order $order, int $sellerId): array
    {
        return (new SellerOrderResource($order, $sellerId))->resolve();
    }

    protected function transformSellerOrderPaginator($paginator, int $sellerId): array
    {
        $items = $paginator->getCollection()
            ->map(fn(Order $order) => $this->transformSellerOrder($order, $sellerId))
            ->values();

        return [
            'data' => $items,
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }

    protected function resolveCoupon(string $code, float $subtotal, int $userId): Coupon|array
    {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return ApiResponseBuilder::error('Invalid coupon', 404);
        }

        if (
            ($coupon->starts_at && now()->lt($coupon->starts_at)) ||
            ($coupon->expires_at && now()->gt($coupon->expires_at))
        ) {
            return ApiResponseBuilder::error('Coupon expired', 400);
        }

        if (CouponUser::where('coupon_id', $coupon->id)->where('user_id', $userId)->exists()) {
            return ApiResponseBuilder::error('Coupon already used', 400);
        }

        if ($subtotal < $coupon->min_order_amount) {
            return ApiResponseBuilder::error('Order not eligible for this coupon', 400);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ApiResponseBuilder::error('Coupon usage limit reached', 400);
        }

        return $coupon;
    }

    protected function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percent'
            ? ($subtotal * $coupon->value) / 100
            : $coupon->value;

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return $discount;
    }

    protected function syncOrderStockForStatusChange(Order $order, string $newStatus): void
    {
        if ($order->status === $newStatus) {
            return;
        }

        if ($order->status !== 'cancelled' && $newStatus === 'cancelled') {
            $this->restoreOrderStock($order);

            return;
        }

        if ($order->status === 'cancelled' && $newStatus !== 'cancelled') {
            $this->reserveOrderStock($order);
        }
    }

    protected function reserveOrderStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (!$item->variant_id) {
                continue;
            }

            $variant = product_variants::query()
                ->lockForUpdate()
                ->find($item->variant_id);

            if (!$variant) {
                throw new \RuntimeException('Variant not found for this order item');
            }

            if ($variant->stock < $item->quantity) {
                throw new \RuntimeException('Insufficient stock to reactivate this order');
            }

            $variant->decrement('stock', $item->quantity);
        }
    }

    protected function restoreOrderStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (!$item->variant_id) {
                continue;
            }

            $variant = product_variants::query()
                ->lockForUpdate()
                ->find($item->variant_id);

            if (!$variant) {
                continue;
            }

            $variant->increment('stock', $item->quantity);
        }
    }

    protected function shouldRestoreStockWhenDeleting(Order $order): bool
    {
        return in_array($order->status, ['pending', 'processing'], true);
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }

    protected function isSeller(?User $user): bool
    {
        return $user && (int) $user->role_id === 2;
    }
}
