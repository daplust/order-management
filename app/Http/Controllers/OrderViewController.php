<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class OrderViewController extends Controller
{
    /**
     * List Order - Show all orders (PRD requirement)
     */
    public function index(): Response
    {
        $orders = Order::with(['table', 'items.food'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'table_number' => $order->table->table_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toISOString(),
                    'items_count' => $order->items->count(),
                ];
            }),
        ]);
    }

    /**
     * Detail Order - Show order details (PRD requirement)
     */
    public function show(Order $order): Response
    {
        $order->load(['table', 'items.food']);
        
        $foods = Food::where('is_available', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'table_id' => $order->table_id,
                'table_number' => $order->table->table_number,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'tax_amount' => $order->tax_amount,
                'service_charge' => $order->service_charge,
                'created_at' => $order->created_at->toISOString(),
                'notes' => $order->notes,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'food_id' => $item->food_id,
                        'food_name' => $item->food->name,
                        'description' => $item->food->description,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                        'special_instructions' => $item->special_instructions,
                    ];
                }),
            ],
            'foods' => $foods->map(function ($food) {
                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'description' => $food->description,
                    'price' => $food->price,
                    'type' => $food->type,
                    'is_available' => $food->is_available,
                ];
            }),
        ]);
    }

    /**
     * Open Order - Create new order for empty table (PRD: Pelayan can open order)
     */
    public function create(Request $request): Response
    {
        $table = Table::findOrFail($request->query('table_id'));
        
        // Check if table is available
        if (!$table->is_available) {
            return redirect()->route('dashboard')->with('error', 'Table is not available');
        }
        
        $foods = Food::where('is_available', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return Inertia::render('Orders/Create', [
            'table' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
            ],
            'foods' => $foods->map(function ($food) {
                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'description' => $food->description,
                    'price' => $food->price,
                    'type' => $food->type,
                    'is_available' => $food->is_available,
                ];
            }),
        ]);
    }

    /**
     * Store new order - Create order with items (PRD: Pelayan can open order)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:food,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                // Get the table and mark it as unavailable
                $table = Table::findOrFail($validated['table_id']);
                
                if (!$table->is_available) {
                    throw new \Exception('Table is not available');
                }
                
                $table->update(['is_available' => false]);

                // Create the order
                $order = Order::create([
                    'table_id' => $table->id,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);

                $totalAmount = 0;

                // Create order items
                foreach ($validated['items'] as $item) {
                    $food = Food::findOrFail($item['food_id']);
                    $subtotal = $food->price * $item['quantity'];
                    $totalAmount += $subtotal;

                    $order->items()->create([
                        'food_id' => $food->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $food->price,
                        'subtotal' => $subtotal,
                        'special_instructions' => $item['special_instructions'] ?? null,
                    ]);
                }

                // Update order total
                $order->update(['total_amount' => $totalAmount]);

                return $order;
            });

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Add items to existing order (PRD: Tambah Makanan ke Order)
     */
    public function addItems(Order $order, Request $request): RedirectResponse
    {
        \Log::info('Add items request received', [
            'order_id' => $order->id,
            'user' => auth()->user()->email,
            'user_roles' => auth()->user()->roles->pluck('name'),
            'request_data' => $request->all(),
        ]);

        // Only allow adding items to open orders
        if ($order->status !== 'open') {
            \Log::warning('Attempted to add items to closed order', ['order_id' => $order->id]);
            return redirect()->back()
                ->with('error', 'Cannot add items to a closed order');
        }

        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.food_id' => 'required|exists:food,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.special_instructions' => 'nullable|string',
            ], [
                'items.required' => 'Please add at least one item',
                'items.*.food_id.required' => 'Food ID is required for each item',
                'items.*.food_id.exists' => 'One or more food items do not exist',
                'items.*.quantity.required' => 'Quantity is required for each item',
                'items.*.quantity.integer' => 'Quantity must be a number',
                'items.*.quantity.min' => 'Quantity must be at least 1',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'messages' => $e->getMessage(),
            ]);
            throw $e;
        }

        try {
            DB::transaction(function () use ($order, $validated) {
                $additionalAmount = 0;

                // Add new items to the order
                foreach ($validated['items'] as $item) {
                    $food = Food::findOrFail($item['food_id']);
                    $subtotal = $food->price * $item['quantity'];
                    $additionalAmount += $subtotal;

                    $order->items()->create([
                        'food_id' => $food->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $food->price,
                        'subtotal' => $subtotal,
                        'special_instructions' => $item['special_instructions'] ?? null,
                    ]);
                }

                // Update order total
                $newTotal = $order->total_amount + $additionalAmount;
                $order->update(['total_amount' => $newTotal]);
                
                \Log::info('Items added successfully', [
                    'order_id' => $order->id,
                    'additional_amount' => $additionalAmount,
                    'new_total' => $newTotal,
                ]);
            });

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Items added to order successfully');
        } catch (\Exception $e) {
            \Log::error('Error adding items to order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to add items: ' . $e->getMessage());
        }
    }

    /**
     * Close order - Mark order as closed and generate receipt (PRD: Tutup Order)
     */
    public function close(Order $order): RedirectResponse
    {
        if ($order->status === 'closed') {
            return redirect()->back()
                ->with('error', 'Order is already closed');
        }

        try {
            DB::transaction(function () use ($order) {
                // First, merge duplicate items (same food_id)
                $itemsByFood = $order->items->groupBy('food_id');
                
                foreach ($itemsByFood as $foodId => $items) {
                    if ($items->count() > 1) {
                        // Multiple items with same food_id - merge them
                        $totalQuantity = $items->sum('quantity');
                        $unitPrice = $items->first()->unit_price;
                        $newSubtotal = $unitPrice * $totalQuantity;
                        
                        // Keep the first item and update it
                        $firstItem = $items->first();
                        $firstItem->update([
                            'quantity' => $totalQuantity,
                            'subtotal' => $newSubtotal,
                        ]);
                        
                        // Delete the duplicate items
                        $items->slice(1)->each(function ($item) {
                            $item->delete();
                        });
                    }
                }
                
                // Recalculate order total after merging
                $order->refresh();
                $subtotal = $order->items->sum('subtotal');
                
                // Calculate tax and service charge
                $tax = $subtotal * 0.10; // 10% tax
                $serviceCharge = $subtotal * 0.05; // 5% service charge
                
                $order->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'tax_amount' => $tax,
                    'service_charge' => $serviceCharge,
                    'total_amount' => $subtotal + $tax + $serviceCharge,
                ]);

                // Mark table as available
                $order->table->update(['is_available' => true]);
            });

            return redirect()->route('orders.receipt', $order->id)
                ->with('success', 'Order closed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate and display receipt (PRD: Generate Receipt)
     */
    public function receipt(Order $order): Response
    {
        $order->load(['table', 'items.food']);
        $subtotal = $order->total_amount - ($order->tax_amount ?? 0) - ($order->service_charge ?? 0);
        $receipt = [
            'receipt_number' => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'order_id' => $order->id,
            'date' => ($order->closed_at ?? now())->timezone('Asia/Jakarta'),
            'table' => [
                'number' => $order->table->number,
                'capacity' => $order->table->capacity,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->food->name,
                    'type' => $item->food->type,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'special_instructions' => $item->special_instructions,
                ];
            }),
            'summary' => [
                'subtotal' => $subtotal,
                'tax' => $subtotal * 0.10,
                'service_charge' => $subtotal * 0.05,
                'grand_total' => $subtotal + ($subtotal * 0.10) + ($subtotal * 0.05),
            ],
            'order_info' => [
                'opened_at' => $order->opened_at,
                'closed_at' => $order->closed_at,
                'status' => $order->status,
                'notes' => $order->notes,
            ],
        ];

        return Inertia::render('Orders/Receipt', [
            'receipt' => $receipt,
        ]);
    }

    /**
     * Download receipt as PDF (PRD: Generate Receipt PDF)
     */
    public function downloadReceiptPdf($id)
    {
        try {
            $order = Order::with(['table', 'items.food'])->findOrFail($id);
            $subtotal = $order->total_amount - ($order->tax_amount ?? 0) - ($order->service_charge ?? 0);
            $receipt = [
                'receipt_number' => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'date' => ($order->closed_at ?? now())->timezone('Asia/Jakarta'),
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
                    'tax' => number_format($subtotal * 0.10, 2),
                    'tax_rate' => '10%',
                    'service_charge' => number_format($subtotal * 0.05, 2),
                    'service_charge_rate' => '5%',
                    'grand_total' => number_format($subtotal + ($subtotal * 0.10) + ($subtotal * 0.05), 2),
                ],
                'order_info' => [
                    'opened_at' => $order->opened_at,
                    'closed_at' => $order->closed_at,
                    'status' => $order->status,
                    'notes' => $order->notes,
                ],
                'payment_status' => $order->status === 'closed' ? 'paid' : 'pending',
            ];
            
            $pdf = Pdf::loadView('receipts.order-receipt', compact('receipt'));
            
            return $pdf->download('receipt-' . $receipt['receipt_number'] . '.pdf');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
