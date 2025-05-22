<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', 'api.security'])->group(function () {
    // User authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Categories
    Route::apiResource('categories', CategoryController::class);
    
    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    
    // Items
    Route::apiResource('items', ItemController::class);
    Route::get('/items/low-stock', [ItemController::class, 'lowStock']);
    
    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/transactions/latest', [TransactionController::class, 'latest']);
});
