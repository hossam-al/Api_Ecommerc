<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Products;
use App\Models\Role;
use App\Models\User;
use App\Models\category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function createProductsWithCategory()
    {
        // إنشاء تصنيف
        $category = category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices',
            'is_active' => true
        ]);

        // إنشاء منتجات
        $product1 = Products::create([
            'name' => 'Smartphone',
            'description' => 'Latest smartphone',
            'price' => 999.99,
            'stock' => 50,
            'is_active' => true,
            'category_id' => $category->id
        ]);

        $product2 = Products::create([
            'name' => 'Laptop',
            'description' => 'Powerful laptop',
            'price' => 1499.99,
            'stock' => 30,
            'is_active' => true,
            'category_id' => $category->id
        ]);

        return [$product1, $product2];
    }

    /**
     * اختبار: المستخدم يمكنه إنشاء طلب جديد
     */
    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product1, $product2] = $this->createProductsWithCategory();

        $orderData = [
            'products' => [
                [
                    'id' => $product1->id,
                    'quantity' => 2
                ],
                [
                    'id' => $product2->id,
                    'quantity' => 1
                ]
            ],
            'notes' => 'Please deliver as soon as possible.'
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully'
            ])
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_number',
                    'total_amount',
                    'status',
                    'notes',
                    'items'
                ]
            ]);

        // التحقق من إنشاء الطلب في قاعدة البيانات
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'notes' => 'Please deliver as soon as possible.'
        ]);

        // التحقق من إنشاء عناصر الطلب
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product1->id,
            'quantity' => 2
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product2->id,
            'quantity' => 1
        ]);
    }

    /**
     * اختبار: المستخدم العادي يمكنه رؤية طلباته فقط
     */
    public function test_user_can_see_only_own_orders(): void
    {
        // إنشاء مستخدمين
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // إنشاء طلب للمستخدم الأول
        $order1 = Order::create([
            'user_id' => $user1->id,
            'order_number' => 'ORD-123456',
            'total_amount' => 2499.97,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'credit_card',
            'shipping_address' => '123 Main St',
            'shipping_phone' => '1234567890'
        ]);

        // إنشاء طلب للمستخدم الثاني
        $order2 = Order::create([
            'user_id' => $user2->id,
            'order_number' => 'ORD-789012',
            'total_amount' => 999.99,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'shipping_address' => '456 Side St',
            'shipping_phone' => '0987654321'
        ]);

        // تسجيل الدخول كالمستخدم الأول
        Sanctum::actingAs($user1);

        // طلب قائمة الطلبات
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $order1->id)
            ->assertJsonMissing(['id' => $order2->id]);
    }

    /**
     * اختبار: Super Admin يمكنه رؤية جميع الطلبات
     */
    public function test_super_admin_can_see_all_orders(): void
    {
        // إنشاء دور Super Admin
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Has all permissions'
        ]);

        // إنشاء مستخدم عادي
        $regularUser = User::factory()->create();

        // إنشاء مستخدم Super Admin
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id
        ]);

        // إنشاء طلبات للمستخدم العادي
        $order1 = Order::create([
            'user_id' => $regularUser->id,
            'order_number' => 'ORD-123456',
            'total_amount' => 2499.97,
            'status' => 'pending',
            'notes' => 'First order'
        ]);

        $order2 = Order::create([
            'user_id' => $regularUser->id,
            'order_number' => 'ORD-789012',
            'total_amount' => 999.99,
            'status' => 'processing',
            'notes' => 'Second order'
        ]);

        // تسجيل الدخول كـ Super Admin
        Sanctum::actingAs($superAdmin);

        // طلب قائمة الطلبات
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'orders')
            ->assertJsonPath('orders.0.id', $order2->id)  // بترتيب تنازلي (الأحدث أولاً)
            ->assertJsonPath('orders.1.id', $order1->id);
    }

    /**
     * اختبار: Admin يمكنه تغيير حالة الطلب
     */
    public function test_admin_can_update_order_status(): void
    {
        // إنشاء دور Admin
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Can manage content'
        ]);

        // إنشاء مستخدم عادي
        $regularUser = User::factory()->create();

        // إنشاء مستخدم Admin
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id
        ]);

        // إنشاء طلب للمستخدم العادي
        $order = Order::create([
            'user_id' => $regularUser->id,
            'order_number' => 'ORD-123456',
            'total_amount' => 2499.97,
            'status' => 'pending',
            'notes' => 'Test order'
        ]);

        // تسجيل الدخول كـ Admin
        Sanctum::actingAs($adminUser);

        // تحديث حالة الطلب
        $response = $this->putJson("/api/v1/orders/{$order->id}", [
            'status' => 'processing',
            'notes' => 'Updated test order'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => [
                    'id' => $order->id,
                    'status' => 'processing',
                    'notes' => 'Updated test order'
                ]
            ]);

        // التحقق من تحديث الطلب في قاعدة البيانات
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'notes' => 'Updated test order'
        ]);
    }
}
