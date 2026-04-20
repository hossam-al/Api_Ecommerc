<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Models\products;
use App\Models\product_variants;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_order_restores_variant_stock_only_once(): void
    {
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrative access',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        ['order' => $order, 'variant' => $variant] = $this->createReservedOrder();

        $service = app(OrderService::class);

        $this->assertSame(3, $variant->fresh()->stock);

        $response = $service->updateStatus($order->id, ['status' => 'cancelled'], $admin);

        $this->assertTrue($response['status']);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $variant->fresh()->stock);

        $response = $service->updateStatus($order->id, ['status' => 'cancelled'], $admin);

        $this->assertTrue($response['status']);
        $this->assertSame(5, $variant->fresh()->stock);
    }

    public function test_deleting_pending_order_restores_variant_stock(): void
    {
        ['order' => $order, 'variant' => $variant, 'customer' => $customer] = $this->createReservedOrder();

        $service = app(OrderService::class);

        $this->assertSame(3, $variant->fresh()->stock);

        $response = $service->destroy($order->id, $customer->id);

        $this->assertTrue($response['status']);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertSame(5, $variant->fresh()->stock);
    }

    private function createReservedOrder(): array
    {
        $seller = User::factory()->create();
        $customer = User::factory()->create();

        $product = products::create([
            'user_id' => $seller->id,
            'brand_id' => null,
            'category_id' => null,
            'name_en' => 'Inventory Test Product',
            'name_ar' => 'Inventory Test Product AR',
            'description_en' => 'Inventory test product',
            'description_ar' => 'Inventory test product AR',
            'primary_image' => 'products/test-product.jpg',
            'is_active' => true,
            'is_featured' => false,
        ]);

        $variant = product_variants::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'price' => 150,
            'stock' => 3,
            'sku' => 'INV-' . Str::upper(Str::random(8)),
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . Str::upper(Str::random(8)),
            'address_title' => 'Home',
            'address_details' => 'Test address',
            'governorate_name' => 'Cairo',
            'shipping_cost' => 25,
            'discount_amount' => 0,
            'total_amount' => 325,
            'status' => 'pending',
            'notes' => 'Inventory sync test',
        ]);

        DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 150,
            'subtotal' => 300,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'order' => $order,
            'variant' => $variant,
            'customer' => $customer,
            'seller' => $seller,
            'product' => $product,
        ];
    }
}
