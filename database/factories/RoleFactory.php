<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Owner',
            'Admin',
            'Customer',
            'Manager',
            'Support',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(10, 9999),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn() => [
            'name' => 'Owner',
            'slug' => 'owner',
            'description' => 'Full system access',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn() => [
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrative access',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn() => [
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'Customer account role',
        ]);
    }
}
