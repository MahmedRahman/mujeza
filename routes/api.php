<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/customers', [AuthController::class, 'apiCustomers'])->name('api.customers.index');
Route::post('/customers', [AuthController::class, 'apiStoreCustomer'])->name('api.customers.store');
Route::post('/customers/check', [AuthController::class, 'apiCheckCustomer'])->name('api.customers.check');
Route::post('/customers/check-and-save', [AuthController::class, 'apiCheckAndSaveCustomer'])->name('api.customers.check-and-save');
Route::patch('/customers/auto-reply/global', [AuthController::class, 'apiUpdateGlobalAutoReply'])->name('api.customers.auto-reply.global');
Route::patch('/customers/auto-reply', [AuthController::class, 'apiUpdateCustomerAutoReply'])->name('api.customers.auto-reply');
Route::get('/customers/{remote_jid}', [AuthController::class, 'apiShowCustomer'])->name('api.customers.show')->where('remote_jid', '.+');
Route::match(['put', 'patch'], '/customers/{remote_jid}', [AuthController::class, 'apiUpdateCustomer'])->name('api.customers.update')->where('remote_jid', '.+');
Route::delete('/customers/{remote_jid}', [AuthController::class, 'apiDestroyCustomer'])->name('api.customers.destroy')->where('remote_jid', '.+');

Route::get('/products', [AuthController::class, 'apiProducts'])->name('api.products.index');
Route::get('/products/text', [AuthController::class, 'apiProductsText'])->name('api.products.text');
Route::get('/products/search', [AuthController::class, 'apiSearchProducts'])->name('api.products.search');
Route::get('/products/search-by-disease', [AuthController::class, 'apiSearchProductsByDisease'])->name('api.products.search-by-disease');
Route::get('/branches', [AuthController::class, 'apiBranches'])->name('api.branches.index');

Route::get('/complaints', [AuthController::class, 'apiComplaints'])->name('api.complaints.index');
Route::post('/complaints', [AuthController::class, 'apiStoreComplaint'])->name('api.complaints.store');
Route::get('/complaints/{complaint}', [AuthController::class, 'apiShowComplaint'])->name('api.complaints.show');
Route::match(['put', 'patch'], '/complaints/{complaint}', [AuthController::class, 'apiUpdateComplaint'])->name('api.complaints.update');
Route::delete('/complaints/{complaint}', [AuthController::class, 'apiDestroyComplaint'])->name('api.complaints.destroy');

Route::get('/orders/status', [AuthController::class, 'apiOrderStatusByPhone'])->name('api.orders.status-by-phone');
Route::get('/orders/by-phone', [AuthController::class, 'apiOrdersByPhone'])->name('api.orders.by-phone');
Route::get('/orders/{order_number}', [AuthController::class, 'apiShowOrder'])->name('api.orders.show')->where('order_number', '[0-9]+');
Route::match(['put', 'patch'], '/orders/{order_number}', [AuthController::class, 'apiUpdateOrder'])->name('api.orders.update')->where('order_number', '[0-9]+');
Route::post('/orders', [AuthController::class, 'apiStoreOrder'])->name('api.orders.store');
Route::get('/agent/prompts', [AuthController::class, 'apiAgentPrompts'])->name('api.agent.prompts');

Route::get('/faqs', [AuthController::class, 'apiFaqs'])->name('api.faqs.index');
Route::get('/faqs/text', [AuthController::class, 'apiFaqsText'])->name('api.faqs.text');
