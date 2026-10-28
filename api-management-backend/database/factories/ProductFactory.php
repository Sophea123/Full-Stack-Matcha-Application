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
            'title' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'image_url' => fake()->randomElement([
                'regular_matcha.png',
                'strawberry_matcha.png',
                'cappuccino.png',
                'iced_latte.png',
                'cream.jpeg',
            ]),
            'price' => fake()->randomFloat(2, 2, 12),
            'category' => fake()->randomElement(['Matcha', 'Coffee', 'Dessert']),
        ];
    }

    public function matcha(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Matcha',
            'image_url' => 'regular_matcha.png',
        ]);
    }

    public function coffee(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Coffee',
            'image_url' => 'cappuccino.png',
        ]);
    }

    public function dessert(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Dessert',
            'image_url' => 'cream.jpeg',
        ]);
    }
}
