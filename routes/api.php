<?php

use App\Http\Controllers\AddressesController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminProductsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\GovernoratesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellerDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:10,5')->post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail']);
    Route::middleware('optional_sanctum')
        ->apiResource('products', ProductsController::class)
        ->only(['index', 'show']);

    Route::middleware(['auth:sanctum', 'seller'])->get(
        '/seller/dashboard/account-status',
        [SellerDashboardController::class, 'accountStatus']
    )->name('seller.dashboard.account-status');

    Route::middleware(['auth:sanctum', 'not_banned'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update', [AuthController::class, 'update']);
        Route::delete('/deleteUser', [AuthController::class, 'deleteUser']);

        Route::get('/products/{id}/reviews', [ProductReviewController::class, 'index']);
        Route::post('/products/{id}/reviews', [ProductReviewController::class, 'store']);
        Route::put('/products/{id}/reviews', [ProductReviewController::class, 'update']);
        Route::delete('/products/{id}/reviews', [ProductReviewController::class, 'destroy']);
        Route::apiResource('categories', CategoryController::class)
            ->only(['index', 'show']);
        Route::apiResource('brands', BrandsController::class)
            ->only(['index', 'show']);
        Route::apiResource('governorates', GovernoratesController::class)
            ->only(['index', 'show']);

        Route::get('/cart', [CartsController::class, 'index']);
        Route::post('/cart', [CartsController::class, 'store']);
        Route::put('/cart/{id}', [CartsController::class, 'update']);
        Route::delete('/cart/{id}', [CartsController::class, 'destroy']);
        Route::delete('/cart', [CartsController::class, 'clear']);

        Route::apiResource('addresses', AddressesController::class)
            ->except(['create', 'edit']);

        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/{id}', [OrderController::class, 'updateOrderUser']);
            Route::put('/{id}', [OrderController::class, 'updateOrderUser']);
            Route::delete('/{id}', [OrderController::class, 'destroy']);
        });

        Route::post('/coupons/validate', [CouponsController::class, 'validateCoupon']);

        Route::middleware('seller')->prefix('seller')->group(function () {
            Route::prefix('dashboard')->group(function () {
                Route::get('/home', [SellerDashboardController::class, 'home'])->name('seller.dashboard.home');
            });

            Route::prefix('products')->group(function () {
                Route::get('/', [ProductsController::class, 'sellerIndex'])->name('seller.products.index');
                Route::post('/', [ProductsController::class, 'sellerStore'])->name('seller.products.store');
                Route::get('/{id}', [ProductsController::class, 'sellerShow'])->name('seller.products.show');
                Route::match(['put', 'patch'], '/{id}', [ProductsController::class, 'sellerUpdate'])->name('seller.products.update');
                Route::delete('/{id}', [ProductsController::class, 'sellerDestroy'])->name('seller.products.destroy');
            });

            Route::prefix('orders')->group(function () {
                Route::get('/', [OrderController::class, 'showSellerOrders'])->name('seller.orders.index');
                Route::get('/{id}', [OrderController::class, 'showSellerOrder'])->name('seller.orders.show');
            });

            Route::prefix('notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'sellerIndex'])->name('seller.notifications.index');
                Route::delete('/', [NotificationController::class, 'destroyAll'])->name('seller.notifications.destroy-all');
                Route::match(['put', 'patch'], '/read-all', [NotificationController::class, 'markAllAsRead'])->name('seller.notifications.read-all');
                Route::match(['put', 'patch'], '/{id}/read', [NotificationController::class, 'markAsRead'])->name('seller.notifications.read');
                Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('seller.notifications.destroy');
            });
        });

        Route::middleware('super_admin')->group(function () {
            Route::get('/users', [AuthController::class, 'listUsers']);
            Route::post('/users/{id}/ban', [AuthController::class, 'banUser']);
            Route::post('/users/{id}/unban', [AuthController::class, 'unbanUser']);

            Route::prefix('admin/analytics')->group(function () {
                Route::get('/summary', [AdminAnalyticsController::class, 'summary'])->name('admin.analytics.summary');
                Route::get('/users', [AdminAnalyticsController::class, 'usersAnalytics'])->name('admin.analytics.users');
                Route::get('/products', [AdminAnalyticsController::class, 'productsAnalytics'])->name('admin.analytics.products');
                Route::get('/orders', [AdminAnalyticsController::class, 'ordersAnalytics'])->name('admin.analytics.orders');
                Route::get('/categories', [AdminAnalyticsController::class, 'categoriesAnalytics'])->name('admin.analytics.categories');
            });

            Route::prefix('admin/notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'adminIndex'])->name('admin.notifications.index');
                Route::delete('/', [NotificationController::class, 'destroyAll'])->name('admin.notifications.destroy-all');
                Route::match(['put', 'patch'], '/read-all', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.read-all');
                Route::match(['put', 'patch'], '/{id}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
                Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
            });

            Route::post('/products', [ProductsController::class, 'store']);
            Route::match(['put', 'patch'], '/products/{id}', [ProductsController::class, 'update']);
            Route::delete('/products/{id}', [ProductsController::class, 'destroy']);
            Route::patch('/admin/products/{productId}/reviews/{reviewId}/status', [ProductReviewController::class, 'moderate']);
            Route::post('/products/delete-image/{id}', [ProductsController::class, 'deleteImage']);
            Route::delete('/products/DeleteAll/delete', [ProductsController::class, 'DeleteAll']);
            Route::get('/admin/products/pending', [ProductsController::class, 'showPendingProducts'])->name('admin.products.pending');
            Route::match(['put', 'patch'], '/admin/products/{id}/activate', [ProductsController::class, 'activateProduct'])->name('admin.products.activate');
            Route::match(['put', 'patch'], '/admin/products/{id}/reject', [ProductsController::class, 'rejectProduct'])->name('admin.products.reject');
            Route::match(['put', 'patch'], '/admin/products/{id}/restore-review', [ProductsController::class, 'restoreRejectedProduct'])->name('admin.products.restore-review');

            Route::post('/categories', [CategoryController::class, 'store']);
            Route::match(['put', 'patch'], '/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
            Route::delete('/categories/DeleteAll/delete', [CategoryController::class, 'DeleteAll']);

            Route::post('/brands', [BrandsController::class, 'store']);
            Route::match(['put', 'patch'], '/brands/{id}', [BrandsController::class, 'update']);
            Route::delete('/brands/{id}', [BrandsController::class, 'destroy']);

            Route::post('/governorates', [GovernoratesController::class, 'store']);
            Route::match(['put', 'patch'], '/governorates/{id}', [GovernoratesController::class, 'update']);
            Route::delete('/governorates/{id}', [GovernoratesController::class, 'destroy']);

            Route::apiResource('coupons', CouponsController::class)
                ->except(['create', 'edit']);

            Route::apiResource('admin-products', AdminProductsController::class)
                ->except(['create', 'edit']);
            Route::delete('/admin-products/DeleteAll/delete', [AdminProductsController::class, 'DeleteAll']);

            Route::apiResource('roles', RoleController::class)
                ->except(['create', 'edit']);
            Route::post('/roles/assign', [RoleController::class, 'assignRole']);

            Route::prefix('orders')->group(function () {
                Route::post('/{id}/updateStatus', [OrderController::class, 'updateStatus']);

                Route::prefix('Admin')->group(function () {
                    Route::get('GetAll', [OrderController::class, 'showAllOrders']);
                    Route::get('/{id}', [OrderController::class, 'showOrderAdmin']);
                    Route::post('Update/{id}', [OrderController::class, 'updateOrderAdmin']);
                    Route::match(['put', 'patch', 'post'], 'UpdateStatus/{id}', [OrderController::class, 'updateStatusAdmin']);
                    Route::delete('Delete/{id}', [OrderController::class, 'destroyOrder']);
                });
            });
        });
    });
});

Route::middleware(['auth:sanctum', 'not_banned'])->get('/user', function (Request $request) {
    return $request->user();
});


