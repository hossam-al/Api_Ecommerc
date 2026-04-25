<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use App\Models\brands;
use App\Models\category;
use App\Models\product_variants;
use App\Models\products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerOrdersTest extends TestCase
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
        return category::create([
            'name_en' => 'Orders Category ' . fake()->unique()->word(),
            'name_ar' => 'فئة الطلبات ' . fake()->unique()->word(),
            'description_en' => 'Orders',
            'description_ar' => 'طلبات',
            'is_active' => true,
        ]);
    }

    protected function createBrand(): brands
    {
        return brands::create([
            'name_en' => 'Orders Brand ' . fake()->unique()->word(),
            'name_ar' => 'براند الطلبات ' . fake()->unique()->word(),
            'is_active' => true,
        ]);
    }

    protected function createSellerProduct(User $seller): products
    {
        $product = products::create([
            'user_id' => $seller->id,
            'brand_id' => $this->createBrand()->id,
            'category_id' => $this->createCategory()->id,
            'name_en' => 'Product ' . fake()->unique()->word(),
            'name_ar' => 'منتج ' . fake()->unique()->word(),
            'description_en' => 'Description',
            'description_ar' => 'وصف',
            'primary_image' => 'https://example.com/product.jpg',
            'is_active' => true,
            'review_status' => 'approved',
            'is_featured' => false,
        ]);

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

    public function test_seller_orders_endpoint_supports_filters_sorting_and_pagination(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000601',
        ]);
        $otherSeller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000602',
        ]);
        $customer = User::factory()->create([
            'role_id' => $roles['customer']->id,
            'phone' => '1000000603',
        ]);

        $sellerProduct = $this->createSellerProduct($seller);
        $otherSellerProduct = $this->createSellerProduct($otherSeller);

        $oldOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SO-001',
            'total_amount' => 200,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Home',
            'address_details' => 'Street 1',
            'governorate_name' => 'Cairo',
            'status' => 'pending',
            'notes' => null,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $newOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-SO-002',
            'total_amount' => 300,
            'shipping_cost' => 20,
            'discount_amount' => 0,
            'coupon_code' => null,
            'address_title' => 'Office',
            'address_details' => 'Street 2',
            'governorate_name' => 'Giza',
            'status' => 'completed',
            'notes' => null,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        OrderItem::create([
            'order_id' => $oldOrder->id,
            'product_id' => $sellerProduct->id,
            'variant_id' => $sellerProduct->variants->first()->id,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
        ]);

        OrderItem::create([
            'order_id' => $newOrder->id,
            'product_id' => $sellerProduct->id,
            'variant_id' => $sellerProduct->variants->first()->id,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        OrderItem::create([
            'order_id' => $newOrder->id,
            'product_id' => $otherSellerProduct->id,
            'variant_id' => $otherSellerProduct->variants->first()->id,
            'quantity' => 1,
            'price' => 100,
            'subtotal' => 100,
        ]);

        Sanctum::actingAs($seller);

        $filtered = $this->getJson('/api/v1/seller/orders?status=completed&date_from=' . now()->subDays(2)->toDateString() . '&sort=oldest&per_page=1');

        $filtered->assertStatus(200)
            ->assertJsonPath('results', 1)
            ->assertJsonPath('filters.status', 'completed')
            ->assertJsonPath('filters.sort', 'oldest')
            ->assertJsonPath('filters.per_page', 1)
            ->assertJsonPath('data.data.0.id', $newOrder->id)
            ->assertJsonPath('data.data.0.items.0.product.id', $sellerProduct->id)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonMissing(['name_en' => $otherSellerProduct->name_en]);

        $allOrders = $this->getJson('/api/v1/seller/orders?sort=latest');

        $allOrders->assertStatus(200)
            ->assertJsonPath('results', 2)
            ->assertJsonPath('data.data.0.id', $newOrder->id)
            ->assertJsonPath('data.data.1.id', $oldOrder->id);
    }
}
