<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TableController extends Controller
{
    /**
     * Public page - Guest can see available tables (PRD: Sebagai Tamu, saya dapat melihat daftar meja yang kosong)
     */
    public function publicIndex(): Response
    {
        $tables = Table::with(['currentOrder'])->get();
        
        $mappedTables = $tables->map(function ($table) {
            return [
                'id' => $table->id,
                'number' => $table->number,
                'capacity' => $table->capacity,
                'status' => $table->is_available ? 'available' : 'occupied',
            ];
        })->toArray();
        
        \Log::info('Welcome page tables:', ['count' => count($mappedTables), 'data' => $mappedTables]);
        
        return Inertia::render('Welcome', [
            'tables' => $mappedTables,
        ]);
    }

    /**
     * Dashboard after login - List Meja & Status Meja (PRD requirement)
     */
    public function index(): Response
    {
        $tables = Table::with(['currentOrder'])->get();
        
        $stats = [
            'totalTables' => Table::count(),
            'availableTables' => Table::where('is_available', true)->count(),
            'occupiedTables' => Table::where('is_available', false)->count(),
            'runningOrders' => Order::where('status', 'open')->count(),
        ];

        return Inertia::render('Dashboard', [
            'tables' => $tables,
            'stats' => $stats,
        ]);
    }
}
