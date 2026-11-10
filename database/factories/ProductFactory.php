<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),

            'name' => fake()->words(3, true),

            'description' => fake()->sentence(),

            'price' => fake()->randomFloat(2, 10, 5000),

            'stock' => fake()->numberBetween(0, 100),

            'is_active' => fake()->boolean(90),
        ];
    }
}
