<?php

namespace Database\Seeders;

use App\Models\brands;
use App\Models\category;
use App\Models\Governorate;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Full system access',
            ],
            [
                'slug' => 'seller',
                'name' => 'Seller',
                'description' => 'Can manage own products and related orders',
            ],
            [
                'slug' => 'customer',
                'name' => 'Customer',
                'description' => 'Customer account role',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::query()->updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                ]
            );
        }

        if (category::query()->count() === 0) {
            category::factory()->count(10)->create();
        }

        if (brands::query()->count() === 0) {
            brands::factory()->count(10)->create();
        }

        if (Governorate::query()->count() === 0) {
            Governorate::factory()->count(10)->create();
        }
    }
}
