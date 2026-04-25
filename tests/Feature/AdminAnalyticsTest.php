<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use App\Models\brands;
use App\Models\category;
use App\Models\products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function createRoles(): array
    {
        return [
            'admin' => Role::create([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access',
            ]),
            'seller' => Role::create([
                'name' => 'Seller',
                'slug' => 'seller',
                'description' => 'Seller role',
            ]),
            'customer' => Role::create([
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Customer role',
            ]),
        ];
    }

    protected function createCategory(string $nameEn, string $nameAr): category
    {
        return category::create([
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'description_en' => $nameEn,
            'description_ar' => $nameAr,
            'is_active' => true,
        ]);
    }

    protected function createBrand(string $nameEn, string $nameAr): brands
    {
        return brands::create([
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'is_active' => true,
        ]);
    }

    protected function createProduct(User $seller, category $category, brands $brand, string $name, bool $isActive = true): products
    {
        return products::create([
            'user_id' => $seller->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name_en' => $name,
            'name_ar' => $name,
            'description_en' => $name,
            'description_ar' => $name,
            'primary_image' => 'https://example.com/' . strtolower(str_replace(' ', '-', $name)) . '.jpg',
            'is_active' => $isActive,
            'is_featured' => false,
        ]);
    }

    public function test_admin_can_access_analytics_endpoints_and_get_expected_counts(): void
    {
        $roles = $this->createRoles();

        $admin = User::factory()->create([
            'role_id' => $roles['admin']->id,
            'phone' => '1000000301',
        ]);
        $sellerOne = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000302',
        ]);
        $sellerTwo = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000303',
        ]);
        $customerOne = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000304',
        ]);
        $customerTwo = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000305',
        ]);

        $categoryOne = $this->createCategory('Electronics', 'الكترونيات');
        $categoryTwo = $this->createCategory('Fashion', 'موضة');
        $brand = $this->createBrand('Brand One', 'براند واحد');

        $sellerOneProductActive = $this->createProduct($sellerOne, $categoryOne, $brand, 'Phone', true);
        $sellerOneProductPending = $this->createProduct($sellerOne, $categoryOne, $brand, 'Tablet', false);
        $sellerTwoProductActive = $this->createProduct($sellerTwo, $categoryTwo, $brand, 'Jacket', true);

        $completedOrder = Order::create([
            'user_id' => $customerOne->id,
            'order_number' => 'ORD-AN-001',
            'total_amount' => 300,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Street 1',
            'governorate_name' => 'Cairo',
            'status' => 'completed',
            'notes' => 'Completed order',
        ]);

        $pendingOrder = Order::create([
            'user_id' => $customerTwo->id,
            'order_number' => 'ORD-AN-002',
            'total_amount' => 120,
            'shipping_cost' => 10,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Office',
            'address_details' => 'Street 2',
            'governorate_name' => 'Giza',
            'status' => 'pending',
            'notes' => 'Pending order',
        ]);

        $cancelledOrder = Order::create([
            'user_id' => $customerOne->id,
            'order_number' => 'ORD-AN-003',
            'total_amount' => 80,
            'shipping_cost' => 5,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Street 3',
            'governorate_name' => 'Alex',
            'status' => 'cancelled',
            'notes' => 'Cancelled order',
        ]);

        OrderItem::create([
            'order_id' => $completedOrder->id,
            'product_id' => $sellerOneProductActive->id,
            'variant_id' => null,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
        ]);

        OrderItem::create([
            'order_id' => $completedOrder->id,
            'product_id' => $sellerTwoProductActive->id,
            'variant_id' => null,
            'quantity' => 1,
            'price' => 200,
            'subtotal' => 200,
        ]);

        OrderItem::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $sellerOneProductActive->id,
            'variant_id' => null,
            'quantity' => 1,
            'price' => 120,
            'subtotal' => 120,
        ]);

        OrderItem::create([
            'order_id' => $cancelledOrder->id,
            'product_id' => $sellerOneProductPending->id,
            'variant_id' => null,
            'quantity' => 1,
            'price' => 80,
            'subtotal' => 80,
        ]);

        Sanctum::actingAs($admin);

        $summaryResponse = $this->getJson('/api/v1/admin/analytics/summary');
        $usersResponse = $this->getJson('/api/v1/admin/analytics/users');
        $productsResponse = $this->getJson('/api/v1/admin/analytics/products');
        $ordersResponse = $this->getJson('/api/v1/admin/analytics/orders');
        $categoriesResponse = $this->getJson('/api/v1/admin/analytics/categories');

        $summaryResponse->assertStatus(200)
            ->assertJsonPath('data.total_users', 5)
            ->assertJsonPath('data.total_admins', 1)
            ->assertJsonPath('data.total_sellers', 2)
            ->assertJsonPath('data.total_customers', 2)
            ->assertJsonPath('data.total_products', 3)
            ->assertJsonPath('data.total_active_products', 2)
            ->assertJsonPath('data.total_pending_products', 1)
            ->assertJsonPath('data.total_orders', 3)
            ->assertJsonPath('data.total_revenue', 300)
            ->assertJsonPath('data.total_categories', 2);

        $usersResponse->assertStatus(200)
            ->assertJsonPath('data.totals.total_sellers', 2)
            ->assertJsonPath('data.totals.total_customers', 2);

        $productsResponse->assertStatus(200)
            ->assertJsonPath('data.totals.total_products', 3)
            ->assertJsonPath('data.totals.total_active_products', 2)
            ->assertJsonPath('data.totals.total_pending_products', 1)
            ->assertJsonPath('data.products_per_seller.0.products_count', 2);

        $ordersResponse->assertStatus(200)
            ->assertJsonPath('data.totals.total_orders', 3)
            ->assertJsonPath('data.orders_by_status.pending', 1)
            ->assertJsonPath('data.orders_by_status.completed', 1)
            ->assertJsonPath('data.orders_by_status.cancelled', 1)
            ->assertJsonPath('data.totals.total_revenue', 300)
            ->assertJsonPath('data.orders_per_seller.0.total_orders', 3);

        $categoriesResponse->assertStatus(200)
            ->assertJsonPath('data.totals.total_categories', 2)
            ->assertJsonPath('data.products_per_category.0.products_count', 2);
    }

    public function test_seller_cannot_access_admin_analytics_endpoints(): void
    {
        $roles = $this->createRoles();

        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000306',
        ]);

        Sanctum::actingAs($seller);

        $this->getJson('/api/v1/admin/analytics/summary')
            ->assertStatus(403);
    }
}
