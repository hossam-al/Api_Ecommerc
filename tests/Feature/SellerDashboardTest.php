<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductView;
use App\Models\Role;
use App\Models\User;
use App\Models\brands;
use App\Models\category;
use App\Models\product_variants;
use App\Models\products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createRoles(): array
    {
        return [
            'admin' => Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin']),
            'seller' => Role::create(['name' => 'Seller', 'slug' => 'seller', 'description' => 'Seller']),
            'customer' => Role::create(['name' => 'Customer', 'slug' => 'customer', 'description' => 'Customer']),
        ];
    }

    protected function createCategory(): category
    {
        $suffix = strtoupper(fake()->unique()->bothify('##??'));

        return category::create([
            'name_en' => 'Electronics ' . $suffix,
            'name_ar' => 'الكترونيات ' . $suffix,
            'description_en' => 'Electronics',
            'description_ar' => 'الكترونيات',
            'is_active' => true,
        ]);
    }

    protected function createBrand(): brands
    {
        $suffix = strtoupper(fake()->unique()->bothify('##??'));

        return brands::create([
            'name_en' => 'Brand ' . $suffix,
            'name_ar' => 'براند ' . $suffix,
            'is_active' => true,
        ]);
    }

    protected function createSellerProduct(User $seller, array $overrides = []): products
    {
        $category = $this->createCategory();
        $brand = $this->createBrand();

        $product = products::create(array_merge([
            'user_id' => $seller->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name_en' => 'Product ' . fake()->unique()->word(),
            'name_ar' => 'منتج ' . fake()->unique()->word(),
            'description_en' => 'Description',
            'description_ar' => 'وصف',
            'primary_image' => 'https://example.com/product.jpg',
            'is_active' => true,
            'review_status' => 'approved',
            'is_featured' => false,
        ], $overrides));

        product_variants::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'price' => 100,
            'stock' => 10,
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('####??')),
        ]);

        return $product->fresh(['variants']);
    }

    public function test_seller_home_dashboard_returns_latest_products_orders_and_review_status_counts(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000501',
            'email_verified_at' => now(),
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000502',
            'email_verified_at' => now(),
        ]);
        $customer = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000503',
        ]);

        $published = $this->createSellerProduct($seller, ['name_en' => 'Published', 'review_status' => 'approved', 'is_active' => true, 'created_at' => now()->subMinutes(3), 'updated_at' => now()->subMinutes(3)]);
        $pending = $this->createSellerProduct($seller, ['name_en' => 'Pending', 'review_status' => 'pending', 'is_active' => false, 'created_at' => now()->subMinutes(2), 'updated_at' => now()->subMinutes(2)]);
        $rejected = $this->createSellerProduct($seller, ['name_en' => 'Rejected', 'review_status' => 'rejected', 'is_active' => false, 'created_at' => now()->subMinute(), 'updated_at' => now()->subMinute()]);
        $otherSellerProduct = $this->createSellerProduct($otherSeller, ['name_en' => 'Other seller product']);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SD-001',
            'total_amount' => 300,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Seller street',
            'governorate_name' => 'Cairo',
            'status' => 'processing',
            'notes' => null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $published->id,
            'variant_id' => $published->variants->first()->id,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
        ]);

        ProductView::create([
            'product_id' => $published->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subHours(1),
        ]);
        ProductView::create([
            'product_id' => $published->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subHours(10),
        ]);
        ProductView::create([
            'product_id' => $otherSellerProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subHours(1),
        ]);

        Sanctum::actingAs($seller);

        $response = $this->getJson('/api/v1/seller/dashboard/home');

        $response->assertStatus(200)
            ->assertJsonPath('data.overview.total_products', 3)
            ->assertJsonPath('data.overview.published_products', 1)
            ->assertJsonPath('data.overview.pending_products', 1)
            ->assertJsonPath('data.overview.rejected_products', 1)
            ->assertJsonPath('data.overview.total_orders', 1)
            ->assertJsonPath('data.product_views.today', 2)
            ->assertJsonPath('data.product_views.this_week', 2)
            ->assertJsonPath('data.latest_products.0.id', $rejected->id)
            ->assertJsonPath('data.latest_orders.0.id', $order->id);
    }

    public function test_seller_account_status_endpoint_supports_pending_review_rejected_and_banned(): void
    {
        $roles = $this->createRoles();

        $pendingSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'pending_review',
            'phone' => '1000000504',
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($pendingSeller);

        $this->getJson('/api/v1/seller/dashboard/account-status')
            ->assertStatus(200)
            ->assertJsonPath('data.account_status.status', 'pending_review')
            ->assertJsonPath('data.account_status.is_under_review', true)
            ->assertJsonPath('data.account_status.is_active', false);

        $rejectedSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'rejected',
            'phone' => '1000000505',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($rejectedSeller);

        $this->getJson('/api/v1/seller/dashboard/account-status')
            ->assertStatus(200)
            ->assertJsonPath('data.account_status.status', 'rejected')
            ->assertJsonPath('data.account_status.is_rejected', true)
            ->assertJsonPath('data.account_status.is_active', false);

        $bannedSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'banned',
            'is_banned' => true,
            'phone' => '1000000506',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($bannedSeller);

        $this->getJson('/api/v1/seller/dashboard/account-status')
            ->assertStatus(200)
            ->assertJsonPath('data.account_status.status', 'banned')
            ->assertJsonPath('data.account_status.is_banned', true);
    }
}
