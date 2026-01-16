<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Notification Settings
    Route::get('/settings/notifications', [NotificationSettingController::class, 'edit'])
        ->name('settings.notifications.edit');
    Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])
        ->name('settings.notifications.update');
});
