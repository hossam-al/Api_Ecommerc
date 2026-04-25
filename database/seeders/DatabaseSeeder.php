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
            User::updateOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Admin User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $adminRole->id,
                    'phone' => '0123456788',
                    'email_verified_at' => now(),
                ]
            );

            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $adminRole->id,
                    'phone' => '0123456789',
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($sellerRole) {
            User::updateOrCreate(
                ['email' => 'seller@example.com'],
                [
                    'name' => 'Seller User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $sellerRole->id,
                    'phone' => '0123456790',
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($customerRole) {
            User::updateOrCreate(
                ['email' => 'customer@example.com'],
                [
                    'name' => 'Customer User',
                    'password' => Hash::make('1234678'),
                    'role_id' => $customerRole->id,
                    'phone' => '0123456791',
                    'email_verified_at' => now(),
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
