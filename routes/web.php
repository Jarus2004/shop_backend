<?php

use App\Http\Controllers\Admin\AdminViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.products');
});

Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.products');
    });

    Route::get('/login', [AdminViewController::class, 'login'])->name('admin.login');
    Route::get('/products', [AdminViewController::class, 'products'])->name('admin.products');
    Route::get('/categories', [AdminViewController::class, 'categories'])->name('admin.categories');
    Route::get('/orders', [AdminViewController::class, 'orders'])->name('admin.orders');
});
