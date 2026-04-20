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
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class SellerPermissionsTest extends TestCase
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
                'description' => 'Can manage own products',
            ]),
            'customer' => Role::create([
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Customer role',
            ]),
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
        $category = $overrides['category'] ?? $this->createCategory();
        $brand = $overrides['brand'] ?? $this->createBrand();

        $product = products::create(array_merge([
            'user_id' => $seller->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name_en' => 'Product ' . fake()->unique()->word(),
            'name_ar' => 'منتج ' . fake()->unique()->word(),
            'description_en' => 'Description',
            'description_ar' => 'وصف',
            'primary_image' => 'https://example.com/product.jpg',
            'is_active' => $overrides['is_active'] ?? true,
            'is_featured' => false,
        ], collect($overrides)->except(['category', 'brand'])->all()));

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

    public function test_seller_can_only_see_own_products(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000101',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000102',
        ]);

        $ownProduct = $this->createSellerProduct($seller);
        $otherProduct = $this->createSellerProduct($otherSeller);

        Sanctum::actingAs($seller);

        $sellerResponse = $this->getJson('/api/v1/seller/products');
        $generalResponse = $this->getJson('/api/v1/products');

        $sellerResponse->assertStatus(200);
        $generalResponse->assertStatus(200);

        $sellerIds = collect($sellerResponse->json('data.data'))->pluck('id')->all();
        $generalIds = collect($generalResponse->json('data.data'))->pluck('id')->all();

        $this->assertSame([$ownProduct->id], $sellerIds);
        $this->assertSame([$ownProduct->id], $generalIds);
        $this->assertNotContains($otherProduct->id, $sellerIds);
        $this->assertNotContains($otherProduct->id, $generalIds);
    }

    public function test_seller_cannot_view_update_or_delete_another_seller_product(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000103',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000104',
        ]);

        $otherProduct = $this->createSellerProduct($otherSeller);

        Sanctum::actingAs($seller);

        $this->getJson('/api/v1/seller/products/' . $otherProduct->id)->assertStatus(404);
        $this->patchJson('/api/v1/seller/products/' . $otherProduct->id, [
            'name_en' => 'Updated',
        ])->assertStatus(404);
        $this->deleteJson('/api/v1/seller/products/' . $otherProduct->id)->assertStatus(404);

        $this->assertDatabaseHas('products', [
            'id' => $otherProduct->id,
            'user_id' => $otherSeller->id,
        ]);
    }

    public function test_seller_product_starts_inactive_and_admin_can_activate_it(): void
    {
        $roles = $this->createRoles();
        $category = $this->createCategory();
        $brand = $this->createBrand();

        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000105',
        ]);
        $admin = User::factory()->create([
            'role_id' => $roles['admin']->id,
            'phone' => '1000000106',
        ]);

        $service = Mockery::mock(ImageUploadService::class);
        $service->shouldReceive('processImage')->once()->andReturn('https://example.com/main.jpg');
        $service->shouldReceive('handleMultipleImages')->once()->andReturn(['https://example.com/gallery.jpg']);
        $service->shouldReceive('handleUploadedFiles')->once()->andReturn([]);
        $this->app->instance(ImageUploadService::class, $service);

        Sanctum::actingAs($seller);

        $createResponse = $this->withHeader('Accept', 'application/json')->post('/api/v1/seller/products', [
            'name_en' => 'Seller Product',
            'name_ar' => 'منتج البائع',
            'description_en' => 'Pending product',
            'description_ar' => 'منتج معلق',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'image_url' => UploadedFile::fake()->create('main.jpg', 100, 'image/jpeg'),
            'variants' => [
                [
                    'color' => 'Black',
                    'size' => 'L',
                    'price' => 150,
                    'stock' => 6,
                    'sku' => 'SELLER-PRODUCT-001',
                ],
            ],
            'images' => [
                UploadedFile::fake()->create('gallery.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Product submitted successfully and is pending review',
            ]);

        $productId = $createResponse->json('data.id');

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'user_id' => $seller->id,
            'is_active' => false,
        ]);

        Sanctum::actingAs($admin);

        $activateResponse = $this->patchJson('/api/v1/admin/products/' . $productId . '/activate');

        $activateResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Product activated successfully',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'is_active' => true,
        ]);
    }

    public function test_seller_dashboard_home_returns_only_current_seller_metrics(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000191',
            'email_verified_at' => now(),
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000192',
            'email_verified_at' => now(),
        ]);
        $customer = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000193',
        ]);

        $publishedProduct = $this->createSellerProduct($seller, ['is_active' => true, 'created_at' => now()->subMinutes(2), 'updated_at' => now()->subMinutes(2)]);
        $pendingProduct = $this->createSellerProduct($seller, ['is_active' => false, 'created_at' => now()->subMinute(), 'updated_at' => now()->subMinute()]);
        $otherSellerProduct = $this->createSellerProduct($otherSeller, ['is_active' => true]);

        $sellerOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SELLER-HOME-1',
            'total_amount' => 200,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Seller Street',
            'governorate_name' => 'Cairo',
            'status' => 'processing',
            'notes' => null,
        ]);

        OrderItem::create([
            'order_id' => $sellerOrder->id,
            'product_id' => $publishedProduct->id,
            'variant_id' => $publishedProduct->variants->first()->id,
            'quantity' => 1,
            'price' => 200,
            'subtotal' => 200,
        ]);

        $otherSellerOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SELLER-HOME-2',
            'total_amount' => 300,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Other Street',
            'governorate_name' => 'Giza',
            'status' => 'processing',
            'notes' => null,
        ]);

        OrderItem::create([
            'order_id' => $otherSellerOrder->id,
            'product_id' => $otherSellerProduct->id,
            'variant_id' => $otherSellerProduct->variants->first()->id,
            'quantity' => 1,
            'price' => 300,
            'subtotal' => 300,
        ]);

        ProductView::create([
            'product_id' => $publishedProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subHours(2),
        ]);
        ProductView::create([
            'product_id' => $publishedProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subHours(5),
        ]);
        ProductView::create([
            'product_id' => $publishedProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subDay(),
        ]);
        ProductView::create([
            'product_id' => $pendingProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subDays(8),
        ]);
        ProductView::create([
            'product_id' => $publishedProduct->id,
            'user_id' => $customer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'viewed_at' => now()->subDays(40),
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
            ->assertJsonPath('data.overview.total_products', 2)
            ->assertJsonPath('data.overview.published_products', 1)
            ->assertJsonPath('data.overview.pending_products', 1)
            ->assertJsonPath('data.overview.rejected_products', 0)
            ->assertJsonPath('data.overview.total_orders', 1)
            ->assertJsonPath('data.product_views.today', 2)
            ->assertJsonPath('data.product_views.this_week', 3)
            ->assertJsonPath('data.product_views.this_month', 4)
            ->assertJsonPath('data.latest_products.0.id', $pendingProduct->id)
            ->assertJsonPath('data.latest_orders.0.id', $sellerOrder->id);
    }

    public function test_seller_products_can_be_filtered_by_status(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000194',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000195',
        ]);

        $publishedProduct = $this->createSellerProduct($seller, ['is_active' => true]);
        $pendingProduct = $this->createSellerProduct($seller, ['is_active' => false]);
        $this->createSellerProduct($otherSeller, ['is_active' => true]);

        Sanctum::actingAs($seller);

        $publishedResponse = $this->getJson('/api/v1/seller/products?status=published&per_page=1');
        $pendingResponse = $this->getJson('/api/v1/seller/products?status=pending');

        $publishedResponse->assertStatus(200)
            ->assertJsonPath('filters.status', 'published')
            ->assertJsonPath('filters.per_page', 1)
            ->assertJsonPath('data.data.0.id', $publishedProduct->id)
            ->assertJsonPath('data.data.0.status', 'published');

        $pendingResponse->assertStatus(200)
            ->assertJsonPath('filters.status', 'pending')
            ->assertJsonPath('data.data.0.id', $pendingProduct->id)
            ->assertJsonPath('data.data.0.status', 'pending');
    }

    public function test_product_show_records_a_view_for_active_products(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000196',
        ]);
        $customer = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000197',
        ]);

        $product = $this->createSellerProduct($seller, ['is_active' => true]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/products/' . $product->id)
            ->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);

        $this->assertDatabaseHas('product_views', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
        ]);
    }

    public function test_guest_can_browse_only_published_products(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000200',
        ]);

        $publishedProduct = $this->createSellerProduct($seller, [
            'is_active' => true,
            'review_status' => 'approved',
        ]);
        $pendingProduct = $this->createSellerProduct($seller, [
            'is_active' => false,
            'review_status' => 'pending',
        ]);
        $rejectedProduct = $this->createSellerProduct($seller, [
            'is_active' => false,
            'review_status' => 'rejected',
        ]);

        $indexResponse = $this->getJson('/api/v1/products');

        $indexResponse->assertStatus(200);

        $productIds = collect($indexResponse->json('data.data'))->pluck('id')->all();

        $this->assertSame([$publishedProduct->id], $productIds);

        $this->getJson('/api/v1/products/' . $publishedProduct->id)
            ->assertStatus(200)
            ->assertJsonPath('data.id', $publishedProduct->id);

        $this->getJson('/api/v1/products/' . $pendingProduct->id)->assertStatus(404);
        $this->getJson('/api/v1/products/' . $rejectedProduct->id)->assertStatus(404);
    }

    public function test_products_endpoint_accepts_bearer_token_without_forcing_authentication(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000201',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000202',
        ]);

        $ownProduct = $this->createSellerProduct($seller);
        $otherProduct = $this->createSellerProduct($otherSeller);
        $token = $seller->createToken('seller-products')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/products');

        $response->assertStatus(200);

        $productIds = collect($response->json('data.data'))->pluck('id')->all();

        $this->assertSame([$ownProduct->id], $productIds);
        $this->assertNotContains($otherProduct->id, $productIds);
    }

    public function test_seller_account_status_endpoint_returns_current_seller_only(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000198',
            'email_verified_at' => now(),
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000199',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($seller);

        $response = $this->getJson('/api/v1/seller/dashboard/account-status');

        $response->assertStatus(200)
            ->assertJsonPath('data.seller.id', $seller->id)
            ->assertJsonPath('data.account_status.status', 'approved')
            ->assertJsonPath('data.account_status.seller_status', 'approved')
            ->assertJsonPath('data.account_status.is_active', true)
            ->assertJsonPath('data.account_status.is_approved', true)
            ->assertJsonMissing(['email' => $otherSeller->email]);
    }

    public function test_seller_can_only_see_orders_related_to_own_products(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000107',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000108',
        ]);
        $customer = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000109',
        ]);

        $sellerProduct = $this->createSellerProduct($seller);
        $otherProduct = $this->createSellerProduct($otherSeller);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SELLER-001',
            'total_amount' => 400,
            'shipping_cost' => 50,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => '123 Street',
            'governorate_name' => 'Cairo',
            'status' => 'processing',
            'notes' => 'Test order',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $sellerProduct->id,
            'variant_id' => $sellerProduct->variants->first()->id,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $otherProduct->id,
            'variant_id' => $otherProduct->variants->first()->id,
            'quantity' => 1,
            'price' => 200,
            'subtotal' => 200,
        ]);

        Sanctum::actingAs($seller);

        $response = $this->getJson('/api/v1/seller/orders');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'results' => 1,
            ])
            ->assertJsonPath('data.data.0.id', $order->id)
            ->assertJsonPath('data.data.0.seller_total', 200)
            ->assertJsonPath('data.data.0.items.0.product.id', $sellerProduct->id)
            ->assertJsonMissing(['name_en' => $otherProduct->name_en]);
    }

    public function test_seller_has_read_only_access_to_categories(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'phone' => '1000000110',
        ]);

        $this->createCategory();

        Sanctum::actingAs($seller);

        $this->getJson('/api/v1/categories')->assertStatus(200);
        $this->postJson('/api/v1/categories', [
            'name_en' => 'New Category',
            'name_ar' => 'قسم جديد',
        ])->assertStatus(403);
    }
}
