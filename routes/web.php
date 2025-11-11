<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Public route
Route::get('/', [App\Http\Controllers\TableController::class, 'publicIndex'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - List Meja & Status Meja (after login)
    Route::get('dashboard', [App\Http\Controllers\TableController::class, 'index'])->name('dashboard');
    
    // Master Makanan (Waiter only)
    Route::middleware([\Spatie\Permission\Middleware\RoleMiddleware::class . ':waiter'])->group(function () {
        Route::get('foods', [App\Http\Controllers\FoodController::class, 'index'])->name('foods.index');
        Route::get('foods/create', [App\Http\Controllers\FoodController::class, 'create'])->name('foods.create');
        Route::post('foods', [App\Http\Controllers\FoodController::class, 'store'])->name('foods.store');
        Route::get('foods/{food}/edit', [App\Http\Controllers\FoodController::class, 'edit'])->name('foods.edit');
        Route::put('foods/{food}', [App\Http\Controllers\FoodController::class, 'update'])->name('foods.update');
        Route::delete('foods/{food}', [App\Http\Controllers\FoodController::class, 'destroy'])->name('foods.destroy');
    });
    
    // List Order (both roles)
    Route::get('orders', [App\Http\Controllers\OrderViewController::class, 'index'])->name('orders.index');
    
    // Open Order - Waiter only
    Route::middleware([\Spatie\Permission\Middleware\RoleMiddleware::class . ':waiter'])->group(function () {
        Route::get('orders/create', [App\Http\Controllers\OrderViewController::class, 'create'])->name('orders.create');
        Route::post('orders', [App\Http\Controllers\OrderViewController::class, 'store'])->name('orders.store');
        Route::post('orders/{order}/items', [App\Http\Controllers\OrderViewController::class, 'addItems'])->name('orders.add-items');
    });
    
    // Detail Order (both roles can view)
    Route::get('orders/{order}', [App\Http\Controllers\OrderViewController::class, 'show'])->name('orders.show');
    
    // Order actions (both roles can add items and close orders)
    Route::post('orders/{order}/close', [App\Http\Controllers\OrderViewController::class, 'close'])->name('orders.close');
    Route::get('orders/{order}/receipt', [App\Http\Controllers\OrderViewController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{id}/receipt/pdf', [App\Http\Controllers\OrderViewController::class, 'downloadReceiptPdf'])->name('orders.receipt.pdf');
});

require __DIR__.'/settings.php';
