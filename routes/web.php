<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // POS
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [\App\Http\Controllers\PosController::class, 'search'])->name('pos.search');
    Route::post('/pos/cart/add', [\App\Http\Controllers\PosController::class, 'add'])->name('pos.add');
    Route::post('/pos/cart/qty', [\App\Http\Controllers\PosController::class, 'updateQty'])->name('pos.qty');
    Route::post('/pos/cart/remove', [\App\Http\Controllers\PosController::class, 'remove'])->name('pos.remove');
    Route::post('/pos/cart/clear', [\App\Http\Controllers\PosController::class, 'clear'])->name('pos.clear');
    Route::post('/pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.checkout');

    // Sales — both roles (attendant sees only their own)
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');
    // Daily Financial Position — both roles
Route::get('/positions', [\App\Http\Controllers\DailyPositionController::class, 'index'])->name('positions.index');
Route::post('/positions', [\App\Http\Controllers\DailyPositionController::class, 'store'])->name('positions.store');
Route::put('/positions/{position}', [\App\Http\Controllers\DailyPositionController::class, 'update'])->name('positions.update');

    // Admin only
    Route::middleware('admin')->group(function () {

        Route::resource('products', \App\Http\Controllers\ProductController::class)
            ->except(['show', 'destroy']);

        Route::post(
            'products/{product}/adjust',
            [\App\Http\Controllers\ProductController::class, 'adjust']
        )->name('products.adjust');

        // Services
        Route::get('services', [\App\Http\Controllers\ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [\App\Http\Controllers\ServiceController::class, 'store'])->name('services.store');
        Route::put('services/{service}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('services.update');

        // Users
        Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');

        // Expenses
        Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Void / Unvoid
        Route::post('sales/{sale}/void', [SalesController::class, 'void'])->name('sales.void');
        Route::post('sales/{sale}/unvoid', [SalesController::class, 'unvoid'])->name('sales.unvoid');

        Route::delete('/positions/{position}', [\App\Http\Controllers\DailyPositionController::class, 'destroy'])->name('positions.destroy');
        // Reports
        Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/daily', [\App\Http\Controllers\ReportController::class, 'daily'])->name('reports.daily');
        Route::get('reports/monthly', [\App\Http\Controllers\ReportController::class, 'monthly'])->name('reports.monthly');
        Route::get('reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/voids', [\App\Http\Controllers\ReportController::class, 'voids'])->name('reports.voids');
    });

});