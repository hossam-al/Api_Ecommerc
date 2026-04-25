<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\products;
use App\Support\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function summary(): array
    {
        return ApiResponseBuilder::success('Dashboard summary retrieved successfully', [
            'total_users' => User::count(),
            'total_admins' => User::where('role_id', 1)->count(),
            'total_sellers' => User::where('role_id', 2)->count(),
            'total_customers' => User::where('role_id', 3)->count(),
            'total_products' => products::count(),
            'total_active_products' => products::published()->count(),
            'total_pending_products' => products::pendingReview()->count(),
            'total_rejected_products' => products::rejected()->count(),
            'total_orders' => Order::count(),
            'total_revenue' => (float) Order::where('status', 'completed')->sum('total_amount'),
            'total_categories' => Category::count(),
        ]);
    }

    public function usersAnalytics(Request $request): array
    {
        $latestLimit = $this->resolveLatestLimit($request);

        $latestUsers = User::with('role:id,name,slug')
            ->latest()
            ->limit($latestLimit)
            ->get(['id', 'name', 'email', 'phone', 'role_id', 'is_banned', 'created_at']);

        return ApiResponseBuilder::success('Users analytics retrieved successfully', [
            'totals' => [
                'total_users' => User::count(),
                'total_admins' => User::where('role_id', 1)->count(),
                'total_sellers' => User::where('role_id', 2)->count(),
                'total_customers' => User::where('role_id', 3)->count(),
                'total_banned_users' => User::where('is_banned', true)->count(),
            ],
            'latest_users' => $latestUsers,
        ]);
    }

    public function productsAnalytics(Request $request): array
    {
        $latestLimit = $this->resolveLatestLimit($request);

        $productsPerSeller = User::query()
            ->where('role_id', 2)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->get(['id', 'name', 'email', 'phone']);

        $latestProducts = products::with([
            'user:id,name,email,phone',
            'category:id,name_en,name_ar',
            'brand:id,name_en,name_ar',
        ])->latest()->limit($latestLimit)->get([
            'id', 'user_id', 'category_id', 'brand_id', 'name_en', 'name_ar', 'is_active', 'is_featured', 'created_at',
        ]);

        return ApiResponseBuilder::success('Products analytics retrieved successfully', [
            'totals' => [
                'total_products' => products::count(),
                'total_active_products' => products::published()->count(),
                'total_pending_products' => products::pendingReview()->count(),
                'total_rejected_products' => products::rejected()->count(),
            ],
            'products_per_seller' => $productsPerSeller,
            'latest_products' => $latestProducts,
        ]);
    }

    public function ordersAnalytics(Request $request): array
    {
        $latestLimit = $this->resolveLatestLimit($request);
        $ordersByStatus = Order::query()->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');
        $ordersPerSeller = DB::table('users')
            ->leftJoin('products', 'products.user_id', '=', 'users.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('users.role_id', 2)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->orderByDesc('total_orders')
            ->select([
                'users.id', 'users.name', 'users.email', 'users.phone',
                DB::raw('COUNT(DISTINCT products.id) as products_count'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw("COALESCE(SUM(CASE WHEN orders.status = 'completed' THEN order_items.subtotal ELSE 0 END), 0) as completed_revenue"),
            ])->get();

        $latestOrders = Order::with(['user:id,name,email,phone'])->withCount('items')->latest()->limit($latestLimit)->get([
            'id', 'user_id', 'order_number', 'total_amount', 'shipping_cost', 'discount_amount', 'status', 'created_at',
        ]);

        return ApiResponseBuilder::success('Orders analytics retrieved successfully', [
            'totals' => [
                'total_orders' => Order::count(),
                'pending_orders' => (int) ($ordersByStatus['pending'] ?? 0),
                'processing_orders' => (int) ($ordersByStatus['processing'] ?? 0),
                'completed_orders' => (int) ($ordersByStatus['completed'] ?? 0),
                'cancelled_orders' => (int) ($ordersByStatus['cancelled'] ?? 0),
                'total_revenue' => (float) Order::where('status', 'completed')->sum('total_amount'),
            ],
            'orders_by_status' => [
                'pending' => (int) ($ordersByStatus['pending'] ?? 0),
                'processing' => (int) ($ordersByStatus['processing'] ?? 0),
                'completed' => (int) ($ordersByStatus['completed'] ?? 0),
                'cancelled' => (int) ($ordersByStatus['cancelled'] ?? 0),
            ],
            'orders_per_seller' => $ordersPerSeller,
            'latest_orders' => $latestOrders,
        ]);
    }

    public function categoriesAnalytics(): array
    {
        $categories = Category::query()->withCount('products')->orderByDesc('products_count')->get([
            'id', 'name_en', 'name_ar', 'is_active', 'created_at',
        ]);

        return ApiResponseBuilder::success('Categories analytics retrieved successfully', [
            'totals' => ['total_categories' => Category::count()],
            'products_per_category' => $categories,
        ]);
    }

    protected function resolveLatestLimit(Request $request): int
    {
        return max(1, min((int) $request->integer('latest_limit', 5), 20));
    }
}
