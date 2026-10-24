<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Kasir (semua role)
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::get('/kasir/products', [KasirController::class, 'getProducts'])->name('kasir.products');
    Route::post('/kasir/store', [KasirController::class, 'store'])->name('kasir.store');

    // Transactions (semua role)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');

    // Profile (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Owner only
    Route::middleware('role:owner')->group(function () {
        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');
        Route::get('/products/export-pdf', [ProductController::class, 'exportPdf'])->name('products.exportPdf');

        // Transactions
        Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/store', [SettingController::class, 'updateStore'])->name('settings.updateStore');
        Route::post('/settings/tax', [SettingController::class, 'updateTax'])->name('settings.updateTax');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.storeUser');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.updateUser');
        Route::patch('/settings/users/{user}/toggle', [SettingController::class, 'toggleUser'])->name('settings.toggleUser');
        Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.destroyUser');
    });
});

require __DIR__.'/auth.php';
