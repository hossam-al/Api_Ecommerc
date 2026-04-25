<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Governorate>
 */
class GovernorateFactory extends Factory
{
    protected $model = Governorate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'shipping_cost' => fake()->randomFloat(2, 0, 250),
        ];
    }
}
