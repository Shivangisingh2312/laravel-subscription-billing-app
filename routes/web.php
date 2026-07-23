<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PlanController::class, 'index'])->name('home');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');

    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::put('/subscriptions', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::post('/subscriptions/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::get('/subscriptions/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::get('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/subscription/activate', [AdminSubscriptionController::class, 'activate'])
            ->name('users.subscription.activate');
        Route::post('/users/{user}/subscription/cancel', [AdminSubscriptionController::class, 'cancel'])
            ->name('users.subscription.cancel');
    });

require __DIR__.'/auth.php';
