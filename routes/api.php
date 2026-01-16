<?php

use App\Http\Controllers\Api\HealthCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'general'])->name('api.health');
Route::get('/health/database', [HealthCheckController::class, 'database'])->name('api.health.database');
Route::get('/health/cache', [HealthCheckController::class, 'cache'])->name('api.health.cache');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
