<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            [
                'name' => 'Chicken Rice',
                'description' => 'Fragrant rice served with tender chicken and special sauce',
                'price' => 12.99,
                'type' => 'food',
                'is_available' => true,
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with caesar dressing, croutons, and parmesan',
                'price' => 8.99,
                'type' => 'food',
                'is_available' => true,
            ],
            [
                'name' => 'Chocolate Lava Cake',
                'description' => 'Warm chocolate cake with a molten chocolate center',
                'price' => 6.99,
                'type' => 'food',
                'is_available' => true,
            ],
            [
                'name' => 'Beef Burger',
                'description' => 'Juicy beef patty with cheese, lettuce, tomato, and special sauce',
                'price' => 14.99,
                'type' => 'food',
                'is_available' => true,
            ],
            [
                'name' => 'Mushroom Soup',
                'description' => 'Creamy soup made with fresh mushrooms',
                'price' => 7.99,
                'type' => 'food',
                'is_available' => true,
            ],
            [
                'name' => 'Iced Coffee',
                'description' => 'Freshly brewed coffee served over ice',
                'price' => 4.99,
                'type' => 'beverage',
                'is_available' => true,
            ],
            [
                'name' => 'Fresh Orange Juice',
                'description' => 'Freshly squeezed orange juice',
                'price' => 5.99,
                'type' => 'beverage',
                'is_available' => true,
            ],
            [
                'name' => 'Green Tea',
                'description' => 'Premium Japanese green tea',
                'price' => 3.99,
                'type' => 'beverage',
                'is_available' => true,
            ],
            [
                'name' => 'Coca Cola',
                'description' => 'Classic Coca Cola soft drink',
                'price' => 2.99,
                'type' => 'beverage',
                'is_available' => true,
            ],
            [
                'name' => 'Mango Smoothie',
                'description' => 'Creamy smoothie made with fresh mangoes',
                'price' => 6.99,
                'type' => 'beverage',
                'is_available' => true,
            ],
            [
                'name' => 'Mineral Water',
                'description' => 'Refreshing mineral water',
                'price' => 1.99,
                'type' => 'beverage',
                'is_available' => true,
            ]
        ];

        foreach ($foods as $food) {
            Food::create($food);
        }
    }
}