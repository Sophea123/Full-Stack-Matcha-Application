<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the demo menu. Re-running updates rows by title instead of
     * creating duplicates.
     */
    public function run(): void
    {
        $products = [
            [
                'title' => 'Regular Matcha',
                'description' => 'Classic matcha whisked with milk or oat milk.',
                'image_url' => 'regular_matcha.png',
                'price' => 4.50,
                'category' => 'Matcha',
            ],
            [
                'title' => 'Strawberry Matcha',
                'description' => 'Matcha layered with freshly made strawberry jam.',
                'image_url' => 'strawberry_matcha.png',
                'price' => 5.00,
                'category' => 'Matcha',
            ],
            [
                'title' => 'Coffee Matcha',
                'description' => 'A smooth combination of matcha and espresso.',
                'image_url' => 'matcha_coffee.png',
                'price' => 5.00,
                'category' => 'Matcha',
            ],
            [
                'title' => 'Iced Matcha',
                'description' => 'Refreshing iced matcha for a hot day.',
                'image_url' => 'matcha1.jpeg',
                'price' => 4.50,
                'category' => 'Matcha',
            ],
            [
                'title' => 'Cappuccino',
                'description' => 'Rich and creamy cappuccino with a velvety foam.',
                'image_url' => 'cappuccino.png',
                'price' => 4.00,
                'category' => 'Coffee',
            ],
            [
                'title' => 'Americano',
                'description' => 'Bold black coffee made with freshly ground beans.',
                'image_url' => 'americano.png',
                'price' => 3.00,
                'category' => 'Coffee',
            ],
            [
                'title' => 'Iced Latte',
                'description' => 'Smooth espresso and cold milk over ice.',
                'image_url' => 'iced_latte.png',
                'price' => 3.50,
                'category' => 'Coffee',
            ],
            [
                'title' => 'Cream Choco',
                'description' => 'Chocolate cake made with love.',
                'image_url' => 'cream.jpeg',
                'price' => 4.99,
                'category' => 'Dessert',
            ],
            [
                'title' => 'Lime Cheesecake',
                'description' => 'Creamy cheesecake with a bright lime finish.',
                'image_url' => 'lime_cheesecake.jpg',
                'price' => 5.25,
                'category' => 'Dessert',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['title' => $product['title']],
                $product,
            );
        }
    }
}
