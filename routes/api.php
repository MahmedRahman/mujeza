<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [AuthController::class, 'apiProducts'])->name('api.products.index');
