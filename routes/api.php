<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [AuthController::class, 'apiProducts'])->name('api.products.index');
Route::get('/products/search', [AuthController::class, 'apiSearchProducts'])->name('api.products.search');
Route::get('/branches', [AuthController::class, 'apiBranches'])->name('api.branches.index');

Route::get('/complaints', [AuthController::class, 'apiComplaints'])->name('api.complaints.index');
Route::post('/complaints', [AuthController::class, 'apiStoreComplaint'])->name('api.complaints.store');
Route::get('/complaints/{complaint}', [AuthController::class, 'apiShowComplaint'])->name('api.complaints.show');
Route::match(['put', 'patch'], '/complaints/{complaint}', [AuthController::class, 'apiUpdateComplaint'])->name('api.complaints.update');
Route::delete('/complaints/{complaint}', [AuthController::class, 'apiDestroyComplaint'])->name('api.complaints.destroy');

Route::post('/orders', [AuthController::class, 'apiStoreOrder'])->name('api.orders.store');
Route::get('/orders/status', [AuthController::class, 'apiOrderStatusByPhone'])->name('api.orders.status-by-phone');
