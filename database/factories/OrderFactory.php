<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'total' => fake()->randomFloat(2, 100, 10000),

            'status' => fake()->randomElement([
                'pending',
                'paid',
                'shipped',
                'completed',
                'cancelled',
            ]),
        ];
    }
}
