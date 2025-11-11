<?php

namespace App\Http\Controllers\Api;
use App\Models\Order;
use App\Models\Table;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->handle(function () {
            return Order::with(['table', 'items.food'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

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

    public function store(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $request->validate([
                'table_id' => 'required|exists:tables,id',
                'items' => 'required|array|min:1',
                'items.*.food_id' => 'required|exists:food,id',
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
                'items.*.food_id' => 'required|exists:food,id',
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

    
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        return $this->handle(function () use ($id, $request) {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);
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

            // If order is closed, generate and return receipt
            if ($request->status === 'closed') {
                $receipt = $this->generateReceiptData($order);
                return $this->success([
                    'order' => $order,
                    'receipt' => $receipt
                ], 'Order closed successfully. Receipt generated.');
            }

            return $this->success($order, 'Order status updated successfully');
        });
    }

    
    private function generateReceiptData(Order $order): array
    {
        // Calculate receipt details
        $subtotal = $order->total_amount;
        $tax = $subtotal * 0.10; // 10% tax
        $serviceCharge = $subtotal * 0.05; // 5% service charge
        $grandTotal = $subtotal + $tax + $serviceCharge;

        return [
            'receipt_number' => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'order_id' => $order->id,
            'date' => $order->closed_at ?? now(),
            'table' => [
                'number' => $order->table->number,
                'capacity' => $order->table->capacity,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->food->name,
                    'type' => $item->food->type,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'subtotal' => number_format($item->subtotal, 2),
                    'special_instructions' => $item->special_instructions,
                ];
            }),
            'summary' => [
                'subtotal' => number_format($subtotal, 2),
                'tax' => number_format($tax, 2),
                'tax_rate' => '10%',
                'service_charge' => number_format($serviceCharge, 2),
                'service_charge_rate' => '5%',
                'grand_total' => number_format($grandTotal, 2),
            ],
            'order_info' => [
                'opened_at' => $order->opened_at,
                'closed_at' => $order->closed_at,
                'status' => $order->status,
                'notes' => $order->notes,
            ],
            'payment_status' => $order->status === 'closed' ? 'paid' : 'pending',
        ];
    }

    
    public function generateReceipt(int $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);
            $receipt = $this->generateReceiptData($order);
            return $this->success($receipt, 'Receipt generated successfully');
        });
    }

    
    public function downloadReceiptPdf(int $id)
    {
        // Don't use handle() wrapper for PDF downloads - return binary response directly
        try {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);
            $receipt = $this->generateReceiptDataForPdf($order);
            
            $pdf = Pdf::loadView('receipts.order-receipt', compact('receipt'));
            
            return $pdf->download('receipt-' . $receipt['receipt_number'] . '.pdf');
        } catch (\Throwable $e) {
            // If error occurs, return JSON error response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate PDF receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    private function generateReceiptDataForPdf(Order $order): array
    {
        // Calculate receipt details
        $subtotal = $order->total_amount;
        $tax = $subtotal * 0.10; // 10% tax
        $serviceCharge = $subtotal * 0.05; // 5% service charge
        $grandTotal = $subtotal + $tax + $serviceCharge;

        return [
            'receipt_number' => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'order_id' => $order->id,
            'date' => $order->closed_at ?? now(),
            'table' => [
                'number' => $order->table->number,
                'capacity' => $order->table->capacity,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->food->name,
                    'type' => $item->food->type,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2, '.', ','),
                    'subtotal' => number_format($item->subtotal, 2, '.', ','),
                    'special_instructions' => $item->special_instructions,
                ];
            })->toArray(),
            'summary' => [
                'subtotal' => number_format($subtotal, 2, '.', ','),
                'tax' => number_format($tax, 2, '.', ','),
                'tax_rate' => '10%',
                'service_charge' => number_format($serviceCharge, 2, '.', ','),
                'service_charge_rate' => '5%',
                'grand_total' => number_format($grandTotal, 2, '.', ','),
            ],
            'order_info' => [
                'opened_at' => $order->opened_at,
                'closed_at' => $order->closed_at,
                'status' => $order->status,
                'notes' => $order->notes,
            ],
            'payment_status' => $order->status === 'closed' ? 'paid' : 'pending',
        ];
    }
}

