<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/tables', [TableController::class, 'index']);
Route::get('/tables/available', [TableController::class, 'available']);

Route::get('/test/model-not-found', function() {
    return \App\Models\User::findOrFail(999);
});

Route::get('/test/server-error', function() {
    throw new \Exception('Test server error');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Food management routes (accessible by both waiter and cashier)
    Route::middleware('role:waiter|cashier')->group(function () {
        Route::get('/foods', [FoodController::class, 'index']);
        Route::get('/foods/{id}', [FoodController::class, 'show']);
        
        // Receipt generation (both waiter and cashier can generate receipts)
        Route::get('/orders/{id}/receipt', [OrderController::class, 'generateReceipt']);
        Route::get('/orders/{id}/receipt/pdf', [OrderController::class, 'downloadReceiptPdf']);
    });
    
    // Food management routes (accessible by waiter only)
    Route::middleware('role:waiter')->group(function () {
        Route::post('/foods', [FoodController::class, 'store']);
        Route::put('/foods/{id}', [FoodController::class, 'update']);
        Route::delete('/foods/{id}', [FoodController::class, 'destroy']);
        
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{id}/items', [OrderController::class, 'addItems']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});