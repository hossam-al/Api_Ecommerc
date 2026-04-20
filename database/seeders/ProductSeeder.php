<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get super admin user
        $superAdmin = \App\Models\User::where('email', 'superadmin@example.com')->first();
        
        // Get all categories
        $categories = \App\Models\Category::all();
        
        // Product data
        $products = [
            [
                'name' => 'iPhone 14 Pro Max',
                'description' => 'The latest iPhone with advanced features and A16 Bionic chip',
                'price' => 1099.99,
                'stock' => 50,
                'category_id' => $categories->where('name', 'Smartphones')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Samsung Galaxy S23 Ultra',
                'description' => 'Powerful Android smartphone with S-Pen support and 200MP camera',
                'price' => 1199.99,
                'stock' => 45,
                'category_id' => $categories->where('name', 'Smartphones')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'MacBook Pro 16-inch',
                'description' => 'High-performance laptop with M2 Pro chip and stunning Retina display',
                'price' => 2499.99,
                'stock' => 25,
                'category_id' => $categories->where('name', 'Computers')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Dell XPS 15',
                'description' => 'Premium Windows laptop with Intel Core i9 and NVIDIA GeForce RTX',
                'price' => 1899.99,
                'stock' => 30,
                'category_id' => $categories->where('name', 'Computers')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'description' => 'Industry-leading noise cancelling wireless headphones with exceptional sound quality',
                'price' => 349.99,
                'stock' => 100,
                'category_id' => $categories->where('name', 'Electronics')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Nike Air Jordan',
                'description' => 'Iconic basketball shoes with premium materials and Air cushioning',
                'price' => 189.99,
                'stock' => 75,
                'category_id' => $categories->where('name', 'Footwear')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'LG 65" OLED TV',
                'description' => 'Ultra-thin 4K OLED TV with perfect blacks and vivid colors',
                'price' => 1799.99,
                'stock' => 20,
                'category_id' => $categories->where('name', 'Home Appliances')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Dyson V15 Vacuum',
                'description' => 'Powerful cordless vacuum with laser dust detection',
                'price' => 749.99,
                'stock' => 35,
                'category_id' => $categories->where('name', 'Home Appliances')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Next-gen gaming console with ultra-fast SSD and 3D audio',
                'price' => 499.99,
                'stock' => 15,
                'category_id' => $categories->where('name', 'Gaming')->first()->id ?? $categories->random()->id
            ],
            [
                'name' => 'Levi\'s 501 Original Jeans',
                'description' => 'Classic straight-leg jeans with button fly and signature styling',
                'price' => 69.99,
                'stock' => 150,
                'category_id' => $categories->where('name', 'Clothing')->first()->id ?? $categories->random()->id
            ],
        ];
        
        // Create products
        foreach ($products as $index => $productData) {
            \App\Models\products::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'user_id' => $superAdmin->id,
                'category_id' => $productData['category_id'],
                'image_url' => 'https://picsum.photos/400/300?random=' . ($index + 1),
                'sku' => 'PROD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
                'is_featured' => $index < 5 // Make first 5 products featured
            ]);
        }
    }
}
