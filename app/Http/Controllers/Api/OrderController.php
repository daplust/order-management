<?php

namespace App\Http\Controllers\Api;
use App\Models\Order;
use App\Models\Table;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends ApiController
{
    /**
     * Get all orders with their related data
     */
    public function index(): JsonResponse
    {
        return $this->handle(function () {
            return Order::with(['table', 'items.food'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get details of a specific order
     */
    public function show(int $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);

            return [
                'order' => $order,
                'table' => $order->table,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'food' => $item->food,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'special_instructions' => $item->special_instructions
                    ];
                }),
                'total_amount' => $order->total_amount
            ];
        });
    }

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $request->validate([
                'table_id' => 'required|exists:tables,id',
                'items' => 'required|array|min:1',
                'items.*.food_id' => 'required|exists:foods,id',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string'
            ]);

            $order = DB::transaction(function () use ($request) {
                // Get the table and mark it as unavailable
                $table = Table::findOrFail($request->table_id);
                $table->markAsUnavailable();

                // Create the order
                $order = Order::create([
                    'table_id' => $table->id,
                    'notes' => $request->notes,
                    'opened_at' => now()
                ]);

                $totalAmount = 0;

                // Create order items
                foreach ($request->items as $item) {
                    $food = Food::findOrFail($item['food_id']);
                    $subtotal = $food->price * $item['quantity'];
                    $totalAmount += $subtotal;

                    $order->items()->create([
                        'food_id' => $food->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $food->price,
                        'subtotal' => $subtotal,
                        'special_instructions' => $item['special_instructions'] ?? null
                    ]);
                }

                // Update order total
                $order->update(['total_amount' => $totalAmount]);

                return $order->load('items.food');
            });

            return $this->created($order, 'Order created successfully');
        }, 201);
    }

    /**
     * Add items to an existing order
     */
    public function addItems(int $id, Request $request): JsonResponse
    {
        return $this->handle(function () use ($id, $request) {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);

            // Only allow adding items to open orders
            if ($order->status !== 'open') {
                return $this->error('Cannot add items to a closed order', 400);
            }

            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.food_id' => 'required|exists:foods,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.special_instructions' => 'nullable|string'
            ]);

            DB::transaction(function () use ($order, $request) {
                $additionalAmount = 0;

                // Add new items to the order
                foreach ($request->items as $item) {
                    $food = Food::findOrFail($item['food_id']);
                    $subtotal = $food->price * $item['quantity'];
                    $additionalAmount += $subtotal;

                    $order->items()->create([
                        'food_id' => $food->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $food->price,
                        'subtotal' => $subtotal,
                        'special_instructions' => $item['special_instructions'] ?? null
                    ]);
                }

                // Update order total
                $newTotal = $order->total_amount + $additionalAmount;
                $order->update(['total_amount' => $newTotal]);
            });

            // Reload the order with updated items
            $order->load('items.food');

            return $this->success($order, 'Items added to order successfully');
        });
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        return $this->handle(function () use ($id, $request) {
            $order = Order::with('table')->findOrFail($id);
            $request->validate([
                'status' => 'required|in:open,closed'
            ]);

            $updateData = ['status' => $request->status];

            // If order is closed, set closed_at and mark table as available
            if ($request->status === 'closed') {
                $updateData['closed_at'] = now();
                $order->table->markAsAvailable();
            }

            $order->update($updateData);
            return $order;
        });
    }
}
