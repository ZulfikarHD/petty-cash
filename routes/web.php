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

    return Inertia::render('Dashboard', [
        'stats' => $stats,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

// My Profile Routes (renamed to avoid conflict with settings routes)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('my-profile.show');
    Route::put('/my-profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('my-profile.update');
    Route::put('/my-profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('my-profile.password.update');
});

require __DIR__.'/settings.php';
