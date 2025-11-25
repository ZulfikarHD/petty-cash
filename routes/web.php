<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

// Transaction Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
});

// Category Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
});

// Budget Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('budgets/overview', [\App\Http\Controllers\BudgetController::class, 'overview'])->name('budgets.overview');
    Route::resource('budgets', \App\Http\Controllers\BudgetController::class);
});

// Cash Balance Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cash-balances/{cashBalance}/reconcile', [\App\Http\Controllers\CashBalanceController::class, 'reconcile'])->name('cash-balances.reconcile');
    Route::post('cash-balances/{cashBalance}/reconciliation', [\App\Http\Controllers\CashBalanceController::class, 'storeReconciliation'])->name('cash-balances.store-reconciliation');
    Route::resource('cash-balances', \App\Http\Controllers\CashBalanceController::class)->except(['edit', 'update']);
});

// Approval Workflow Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('approvals', [\App\Http\Controllers\ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{approval}', [\App\Http\Controllers\ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('approvals/{approval}/approve', [\App\Http\Controllers\ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [\App\Http\Controllers\ApprovalController::class, 'reject'])->name('approvals.reject');
});

// Notification Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
});

// My Profile Routes (renamed to avoid conflict with settings routes)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('my-profile.show');
    Route::put('/my-profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('my-profile.update');
    Route::put('/my-profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('my-profile.password.update');
});

require __DIR__.'/settings.php';
