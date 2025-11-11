<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Table;
use App\Models\Food;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $tables = Table::all();
        $foods = Food::all();

        if ($tables->isEmpty() || $foods->isEmpty()) {
            $this->command->info('Please run TableSeeder and ensure there are foods in the database first.');
            return;
        }

        $openOrder = Order::create([
            'table_id' => $tables->first()->id,
            'status' => 'open',
            'notes' => 'Test order 1 - Currently open'
        ]);

        $openOrder->items()->create([
            'food_id' => $foods->first()->id,
            'quantity' => 2,
            'unit_price' => $foods->first()->price,
            'subtotal' => $foods->first()->price * 2
        ]);

        if ($foods->count() > 1) {
            $openOrder->items()->create([
                'food_id' => $foods[1]->id,
                'quantity' => 1,
                'unit_price' => $foods[1]->price,
                'subtotal' => $foods[1]->price,
                'special_instructions' => 'Extra spicy'
            ]);
        }

        $openOrder->update(['total_amount' => $openOrder->items->sum('subtotal')]);
        $tables->first()->markAsUnavailable();

        // Create a closed order
        if ($tables->count() > 1) {
            $closedOrder = Order::create([
                'table_id' => $tables[1]->id,
                'status' => 'closed',
                'notes' => 'Test order 2 - Already closed'
            ]);

            // Add an item to the closed order
            $closedOrder->items()->create([
                'food_id' => $foods->first()->id,
                'quantity' => 3,
                'unit_price' => $foods->first()->price,
                'subtotal' => $foods->first()->price * 3
            ]);

            // Update the total amount
            $closedOrder->update(['total_amount' => $closedOrder->items->sum('subtotal')]);
        }
    }
}
