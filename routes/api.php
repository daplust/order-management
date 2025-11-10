<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Table routes (public)
Route::get('/tables', [TableController::class, 'index']);
Route::get('/tables/available', [TableController::class, 'available']);

// Test routes for error handling
Route::get('/test/model-not-found', function() {
    return \App\Models\User::findOrFail(999);
});

Route::get('/test/server-error', function() {
    throw new \Exception('Test server error');
});

// Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);

//     // Food management routes
//     Route::prefix('foods')->group(function () {
//         // Routes for both waiter and cashier
//         Route::get('/', [FoodController::class, 'index'])->middleware('role:waiter|cashier');
//         Route::get('/{food}', [FoodController::class, 'show'])->middleware('can:view-foods');

//         // Routes for waiter only
//         Route::post('/', [FoodController::class, 'store'])->middleware('can:create-foods');
//         Route::put('/{food}', [FoodController::class, 'update'])->middleware('can:edit-foods');
//         Route::delete('/{food}', [FoodController::class, 'destroy'])->middleware('can:delete-foods');
//     });
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Food management routes (accessible by both waiter and cashier)
    Route::middleware('role:waiter|cashier')->group(function () {
        Route::get('/foods', [FoodController::class, 'index']);
        Route::get('/foods/{id}', [FoodController::class, 'show']);
    });
    
    // Food management routes (accessible by waiter only)
    Route::middleware('role:waiter')->group(function () {
        Route::post('/foods', [FoodController::class, 'store']);
        Route::put('/foods/{id}', [FoodController::class, 'update']);
        Route::delete('/foods/{id}', [FoodController::class, 'destroy']);
        
        // Order routes
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{id}/items', [OrderController::class, 'addItems']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});