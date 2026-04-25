<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
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

        foreach ($categories as $categoryName) {
            Category::updateOrCreate(
                ['name_en' => $categoryName],
                [
                    'name_ar' => $categoryName,
                    'user_id' => $superAdmin->id,
                    'description_en' => 'Description for ' . $categoryName . ' category',
                    'description_ar' => 'Description for ' . $categoryName . ' category',
                    'is_active' => true,
                ]
            );
        }
    }
}
