<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/categories', [AuthController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', [AuthController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AuthController::class, 'storeCategory'])->name('categories.store');
    Route::get('/diseases', [AuthController::class, 'diseases'])->name('diseases.index');
    Route::get('/diseases/create', [AuthController::class, 'createDisease'])->name('diseases.create');
    Route::post('/diseases', [AuthController::class, 'storeDisease'])->name('diseases.store');
    Route::get('/products', [AuthController::class, 'products'])->name('products.index');
    Route::get('/products/create', [AuthController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AuthController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [AuthController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [AuthController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/ai-benefits', [AuthController::class, 'suggestBenefits'])->name('products.ai.benefits');
    Route::post('/products/ai-diseases', [AuthController::class, 'suggestDiseases'])->name('products.ai.diseases');
    Route::post('/products/ai-usage-methods', [AuthController::class, 'suggestUsageMethods'])->name('products.ai.usage');
    Route::delete('/products/{product}', [AuthController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/settings', [AuthController::class, 'settings'])->name('settings.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
