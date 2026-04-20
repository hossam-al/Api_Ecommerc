<?php

namespace Database\Factories;

use App\Models\brands;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<brands>
 */
class BrandFactory extends Factory
{
    protected $model = brands::class;

    public function definition(): array
    {
        return [
            'name_en' => fake()->unique()->company(),
            'name_ar' => fake('ar_EG')->unique()->company(),
            'logo' => fake()->optional()->imageUrl(300, 300, 'business'),
            'is_active' => fake()->boolean(85),
        ];
    }
}
