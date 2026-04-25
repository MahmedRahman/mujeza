<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [AuthController::class, 'apiProducts'])->name('api.products.index');
Route::get('/products/search', [AuthController::class, 'apiSearchProducts'])->name('api.products.search');
