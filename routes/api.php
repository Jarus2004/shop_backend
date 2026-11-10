<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Customer Protected Routes (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| Admin Protected Routes (auth:sanctum + admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Products Management
    Route::get('/products', [ProductController::class, 'adminIndex']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/products/trashed', [ProductController::class, 'trashed']);
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']);

    // Categories Management
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Orders Management
    Route::get('/orders', [OrderController::class, 'adminIndex']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'adminUpdateStatus']);
});
