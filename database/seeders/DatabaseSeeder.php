<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LookupSeeder::class,
        ]);

        $adminRole = Role::where('slug', 'admin')->first();
        $sellerRole = Role::where('slug', 'seller')->first();
        $customerRole = Role::where('slug', 'customer')->first();

        if ($adminRole) {
            User::firstOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Admin User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $adminRole->id,
                    'phone' => '0123456788',
                ]
            );

            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $adminRole->id,
                    'phone' => '0123456789',
                ]
            );
        }

        if ($sellerRole) {
            User::firstOrCreate(
                ['email' => 'seller@example.com'],
                [
                    'name' => 'Seller User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $sellerRole->id,
                    'phone' => '0123456790',
                ]
            );
        }

        if ($customerRole) {
            User::firstOrCreate(
                ['email' => 'customer@example.com'],
                [
                    'name' => 'Customer User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $customerRole->id,
                    'phone' => '0123456791',
                ]
            );
        }

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            AdminProductSeeder::class,
        ]);
    }
}
