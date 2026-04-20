<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get super admin ID for main categories
        $superAdmin = User::where('email', 'superadmin@example.com')->first();

        // If super admin doesn't exist, we can't proceed
        if (!$superAdmin) {
            $this->command->error('Super Admin user not found. Please run DatabaseSeeder first.');
            return;
        }

        // List of category names
        $categories = [
            'Electronics',
            'Home Appliances',
            'Clothing',
            'Footwear',
            'Accessories',
            'Smartphones',
            'Computers',
            'Furniture',
            'Gaming',
            'Pet Supplies'
        ];

        // Create categories
        foreach ($categories as $index => $categoryName) {
            Category::create([
                'name' => $categoryName,
                'user_id' => $superAdmin->id,
                'description' => 'Description for ' . $categoryName . ' category',
                // 'image_url' => 'https://picsum.photos/300/200?random=' . ($index + 1),
                'is_active' => true,
            ]);
        }
    }
}
