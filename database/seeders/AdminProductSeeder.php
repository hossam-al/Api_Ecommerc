<?php

namespace Database\Seeders;

use App\Models\AdminProduct;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = \App\Models\User::where('email', 'admin@example.com')->first();

        // Get all categories
        $categories = \App\Models\Category::all();

        // Product data for admin
        $adminProducts = [
            [
                'name' => 'Custom Gaming PC',
                'description' => 'Built-to-order gaming PC with RTX 4080 and Intel i9 processor',
                'price' => 2499.99,
                'stock' => 10,
                'category_id' => $categories->where('name', 'Computers')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB backlit mechanical keyboard with Cherry MX switches',
                'price' => 129.99,
                'stock' => 50,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'description' => 'Premium office chair with lumbar support and adjustable features',
                'price' => 349.99,
                'stock' => 25,
                'category_id' => $categories->where('name', 'Furniture')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Wireless Gaming Mouse',
                'description' => 'Ultra-responsive wireless mouse with 25K DPI sensor',
                'price' => 89.99,
                'stock' => 75,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Smart Watch Series 8',
                'description' => 'Advanced fitness tracker with ECG and blood oxygen monitoring',
                'price' => 399.99,
                'stock' => 40,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Designer Sunglasses',
                'description' => 'Polarized UV protection sunglasses with premium frames',
                'price' => 159.99,
                'stock' => 60,
                'category_id' => $categories->where('name', 'Accessories')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Bluetooth Earbuds',
                'description' => 'True wireless earbuds with active noise cancellation',
                'price' => 179.99,
                'stock' => 100,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Smart Home Hub',
                'description' => 'Central controller for all your smart home devices',
                'price' => 129.99,
                'stock' => 30,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Premium Dog Food',
                'description' => 'Grain-free, high-protein dog food for all breeds',
                'price' => 59.99,
                'stock' => 200,
                'category_id' => $categories->where('name', 'Pet Supplies')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Leather Messenger Bag',
                'description' => 'Handcrafted full-grain leather bag with laptop compartment',
                'price' => 199.99,
                'stock' => 35,
                'category_id' => $categories->where('name', 'Accessories')->first()->id ?? $categories->random()->id
            ],
        ];

        // Create admin products
        foreach ($adminProducts as $index => $productData) {
            AdminProduct::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'user_id' => $admin->id,
                'category_id' => $productData['category_id'],
                'image_url' => 'https://picsum.photos/400/300?random=' . ($index + 20), // Different random images
                'sku' => 'ADMIN' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
                'is_featured' => $index < 3 // Make first 3 products featured
            ]);
        }
    }
}
