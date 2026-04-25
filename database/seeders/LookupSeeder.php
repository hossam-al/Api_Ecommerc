<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Governorate;
use App\Models\Role;
use App\Models\brands;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'admin', 'name' => 'Admin', 'description' => 'Full system access'],
            ['slug' => 'seller', 'name' => 'Seller', 'description' => 'Can manage own products'],
            ['slug' => 'customer', 'name' => 'Customer', 'description' => 'Customer account'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                ]
            );
        }

        $categories = [
            ['name_en' => 'Bedrooms', 'name_ar' => 'غرف نوم'],
            ['name_en' => 'Living Rooms', 'name_ar' => 'انتريهات'],
            ['name_en' => 'Salons', 'name_ar' => 'صالونات'],
            ['name_en' => 'Dining Rooms', 'name_ar' => 'سفرة'],
            ['name_en' => 'Kids Rooms', 'name_ar' => 'أطفال'],
            ['name_en' => 'Offices', 'name_ar' => 'مكاتب'],
            ['name_en' => 'Kitchens', 'name_ar' => 'مطابخ'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name_en' => $category['name_en']],
                [
                    'name_ar' => $category['name_ar'],
                    'is_active' => true,
                ]
            );
        }

        $brands = [
            ['name_en' => 'Modern Home', 'name_ar' => 'مودرن هوم'],
            ['name_en' => 'Classic Wood', 'name_ar' => 'كلاسيك وود'],
            ['name_en' => 'Damietta Furniture', 'name_ar' => 'أثاث دمياط'],
            ['name_en' => 'Royal Design', 'name_ar' => 'رويال ديزاين'],
        ];

        foreach ($brands as $brand) {
            brands::updateOrCreate(
                ['name_en' => $brand['name_en']],
                [
                    'name_ar' => $brand['name_ar'],
                    'is_active' => true,
                ]
            );
        }

        $governorates = ['القاهرة', 'الجيزة', 'الإسكندرية', 'دمياط', 'الدقهلية', 'الشرقية'];

        foreach ($governorates as $governorate) {
            Governorate::updateOrCreate(['name' => $governorate]);
        }
    }
}
