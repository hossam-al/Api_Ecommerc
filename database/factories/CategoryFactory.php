<?php

namespace Database\Factories;

use App\Models\category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<category>
 */
class CategoryFactory extends Factory
{
    protected $model = category::class;

    public function definition(): array
    {
        return [
            'name_en' => fake()->unique()->words(2, true),
            'name_ar' => fake('ar_EG')->unique()->words(2, true),
            'description_en' => fake()->optional()->sentence(10),
            'description_ar' => fake('ar_EG')->optional()->sentence(10),
            'is_active' => fake()->boolean(85),
        ];
    }
}
