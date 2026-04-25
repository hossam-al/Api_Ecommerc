<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\product_variants;
use App\Models\products;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();

        if (!$superAdmin) {
            $this->command->error('Super Admin user not found. Please run DatabaseSeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'iPhone 14 Pro Max',
                'description' => 'The latest iPhone with advanced features and A16 Bionic chip',
                'price' => 1099.99,
                'stock' => 50,
                'category' => 'Smartphones',
            ],
            [
                'name' => 'Samsung Galaxy S23 Ultra',
                'description' => 'Powerful Android smartphone with S-Pen support and 200MP camera',
                'price' => 1199.99,
                'stock' => 45,
                'category' => 'Smartphones',
            ],
            [
                'name' => 'MacBook Pro 16-inch',
                'description' => 'High-performance laptop with M2 Pro chip and stunning Retina display',
                'price' => 2499.99,
                'stock' => 25,
                'category' => 'Computers',
            ],
            [
                'name' => 'Dell XPS 15',
                'description' => 'Premium Windows laptop with Intel Core i9 and NVIDIA GeForce RTX',
                'price' => 1899.99,
                'stock' => 30,
                'category' => 'Computers',
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'description' => 'Industry-leading noise cancelling wireless headphones with exceptional sound quality',
                'price' => 349.99,
                'stock' => 100,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Nike Air Jordan',
                'description' => 'Iconic basketball shoes with premium materials and Air cushioning',
                'price' => 189.99,
                'stock' => 75,
                'category' => 'Footwear',
            ],
            [
                'name' => 'LG 65" OLED TV',
                'description' => 'Ultra-thin 4K OLED TV with perfect blacks and vivid colors',
                'price' => 1799.99,
                'stock' => 20,
                'category' => 'Home Appliances',
            ],
            [
                'name' => 'Dyson V15 Vacuum',
                'description' => 'Powerful cordless vacuum with laser dust detection',
                'price' => 749.99,
                'stock' => 35,
                'category' => 'Home Appliances',
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Next-gen gaming console with ultra-fast SSD and 3D audio',
                'price' => 499.99,
                'stock' => 15,
                'category' => 'Gaming',
            ],
            [
                'name' => 'Levi\'s 501 Original Jeans',
                'description' => 'Classic straight-leg jeans with button fly and signature styling',
                'price' => 69.99,
                'stock' => 150,
                'category' => 'Clothing',
            ],
        ];

        foreach ($products as $index => $productData) {
            $category = Category::where('name_en', $productData['category'])->first()
                ?? Category::inRandomOrder()->first();

            $primaryImage = 'https://picsum.photos/400/300?random=' . ($index + 1);

            $product = products::updateOrCreate(
                ['name_en' => $productData['name']],
                [
                    'name_ar' => $productData['name'],
                    'description_en' => $productData['description'],
                    'description_ar' => $productData['description'],
                    'primary_image' => $primaryImage,
                    'user_id' => $superAdmin->id,
                    'category_id' => $category?->id,
                    'is_active' => true,
                    'is_featured' => $index < 5,
                ]
            );

            product_variants::updateOrCreate(
                ['sku' => 'PROD' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'product_id' => $product->id,
                    'color' => 'Default',
                    'size' => 'Standard',
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'primary_image' => $primaryImage,
                ]
            );
        }
    }
}
