<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FoodController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Test routes for error handling
Route::get('/test/model-not-found', function() {
    return \App\Models\User::findOrFail(999);
});

Route::get('/test/server-error', function() {
    throw new \Exception('Test server error');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Food management routes (accessible by both waiter and cashier)
    Route::middleware('role:waiter,cashier')->group(function () {
        Route::get('/foods', [FoodController::class, 'index']);
        Route::get('/foods/{food}', [FoodController::class, 'show']);
    });
    
    // Food management routes (accessible by waiter only)
    Route::middleware('role:waiter')->group(function () {
        Route::post('/foods', [FoodController::class, 'store']);
        Route::put('/foods/{food}', [FoodController::class, 'update']);
        Route::delete('/foods/{food}', [FoodController::class, 'destroy']);
    });
});