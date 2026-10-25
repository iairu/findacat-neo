<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminCatController;
use App\Http\Controllers\Api\CatController;
use App\Http\Controllers\Api\LookupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => [
    'status' => 'ok',
    'app' => config('app.name'),
]);

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/resend', [AuthController::class, 'resendVerification'])->middleware('throttle:3,1');
});

Route::get('/breeds', [LookupController::class, 'breeds']);
Route::get('/ems', [LookupController::class, 'ems']);
Route::get('/stats', [LookupController::class, 'stats']);
Route::get('/birthdays', [LookupController::class, 'birthdays']);
Route::get('/test-mating', [LookupController::class, 'testMating']);

Route::get('/cats', [CatController::class, 'index']);
Route::get('/cats/{cat}', [CatController::class, 'show']);
Route::get('/cats/{cat}/pedigree', [CatController::class, 'pedigree']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/my/cats', [CatController::class, 'mine']);
    Route::post('/cats', [CatController::class, 'store']);
    Route::put('/cats/{cat}', [CatController::class, 'update']);
    Route::delete('/cats/{cat}', [CatController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'verified', 'can:admin'])->prefix('admin')->group(function () {
    Route::get('/cats', [AdminCatController::class, 'index']);
    Route::post('/cats/{cat}/approve', [AdminCatController::class, 'approve']);
    Route::post('/cats/{cat}/reject', [AdminCatController::class, 'reject']);
});
