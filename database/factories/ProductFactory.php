<?php

namespace Database\Factories;

use App\Models\Product;
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
            'code' => fake()->unique()->bothify('PR-####'),
            'name' => fake()->words(2, true),
            'category' => 'makanan',
            'unit' => 'Porsi',
            'buy_price' => 10000,
            'sell_price' => 20000,
            'stock' => 50,
            'min_stock' => 5,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the product is hidden from the cashier screen.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
