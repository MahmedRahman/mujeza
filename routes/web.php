<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WhatsAppController;
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

    Route::get('/branches', [AuthController::class, 'branches'])->name('branches.index');
    Route::get('/branches/create', [AuthController::class, 'createBranch'])->name('branches.create');
    Route::post('/branches', [AuthController::class, 'storeBranch'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [AuthController::class, 'editBranch'])->name('branches.edit');
    Route::put('/branches/{branch}', [AuthController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}', [AuthController::class, 'destroyBranch'])->name('branches.destroy');

    Route::get('/orders', [AuthController::class, 'orders'])->name('orders.index');
    Route::get('/orders/create', [AuthController::class, 'createOrder'])->name('orders.create');
    Route::post('/orders', [AuthController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders/{order}/edit', [AuthController::class, 'editOrder'])->name('orders.edit');
    Route::put('/orders/{order}', [AuthController::class, 'updateOrder'])->name('orders.update');
    Route::delete('/orders/{order}', [AuthController::class, 'destroyOrder'])->name('orders.destroy');
    Route::get('/orders/{order}', [AuthController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [AuthController::class, 'invoiceOrder'])->name('orders.invoice');

    Route::get('/complaints', [AuthController::class, 'complaints'])->name('complaints.index');
    Route::get('/complaints/create', [AuthController::class, 'createComplaint'])->name('complaints.create');
    Route::post('/complaints', [AuthController::class, 'storeComplaint'])->name('complaints.store');
    Route::get('/complaints/{complaint}/edit', [AuthController::class, 'editComplaint'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [AuthController::class, 'updateComplaint'])->name('complaints.update');
    Route::delete('/complaints/{complaint}', [AuthController::class, 'destroyComplaint'])->name('complaints.destroy');

    Route::get('/settings', [AuthController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [AuthController::class, 'updateSettings'])->name('settings.update');
    Route::get('/conversations', [AuthController::class, 'conversations'])->name('conversations.index');

    // Evolution API proxy (WhatsApp Chat)
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/status', [WhatsAppController::class, 'status'])->name('status');
        Route::get('/chats', [WhatsAppController::class, 'chats'])->name('chats');
        Route::get('/auto-reply/numbers', [WhatsAppController::class, 'autoReplyNumbers'])->name('auto_reply.numbers');
        Route::get('/auto-reply/number-status', [WhatsAppController::class, 'autoReplyNumberStatus'])->name('auto_reply.number_status');
        Route::get('/auto-reply/settings', [WhatsAppController::class, 'autoReplySettings'])->name('auto_reply.settings');
        Route::post('/auto-reply/global', [WhatsAppController::class, 'updateGlobalAutoReply'])->name('auto_reply.global');
        Route::post('/auto-reply/chat', [WhatsAppController::class, 'updateChatAutoReply'])->name('auto_reply.chat');
        Route::get('/messages', [WhatsAppController::class, 'messages'])->name('messages');
        Route::post('/send', [WhatsAppController::class, 'send'])->name('send');
        Route::get('/debug', [WhatsAppController::class, 'debug'])->name('debug');
    });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
