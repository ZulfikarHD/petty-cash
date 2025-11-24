<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $stats = [];

    if (auth()->user()->can('view-users')) {
        $stats['totalUsers'] = \App\Models\User::count();
        $stats['verifiedUsers'] = \App\Models\User::whereNotNull('email_verified_at')->count();
        $stats['recentUsers'] = \App\Models\User::where('created_at', '>=', now()->subDays(30))->count();
    }

    if (auth()->user()->can('view-transactions')) {
        $stats['totalTransactions'] = \App\Models\Transaction::count();
        $stats['pendingTransactions'] = \App\Models\Transaction::where('status', 'pending')->count();
        $stats['todayCashIn'] = \App\Models\Transaction::where('type', 'in')
            ->whereDate('transaction_date', today())
            ->sum('amount');
        $stats['todayCashOut'] = \App\Models\Transaction::where('type', 'out')
            ->whereDate('transaction_date', today())
            ->sum('amount');

        // Recent transactions
        $stats['recentTransactions'] = \App\Models\Transaction::with('user')
            ->latest('transaction_date')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'transaction_date' => $transaction->transaction_date,
                'status' => $transaction->status,
                'user' => [
                    'name' => $transaction->user->name,
                ],
            ]);
    }

    return Inertia::render('Dashboard', [
        'stats' => $stats,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

// Transaction Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
});

// My Profile Routes (renamed to avoid conflict with settings routes)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('my-profile.show');
    Route::put('/my-profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('my-profile.update');
    Route::put('/my-profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('my-profile.password.update');
});

require __DIR__.'/settings.php';
