<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomersController;

Route::get('/health', fn() => ['ok' => true]);
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/me', fn() => request()->user());
// });
Route::controller(AuthController::class)->group(function () {
    // 公開ルート
    Route::post('/login', 'login')->middleware('throttle:login');
    Route::post('/refresh', 'refresh')->middleware('throttle:refresh');

    // 認証が必要なルートを内部でまとめる（ミドルウェア重複を削減）
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::controller(CustomersController::class)->group(function () {
        Route::post('/customers/regist', 'regist');
        Route::post('/customers/update', 'update');
        Route::post('/customers/delete', 'delete');
    });
});
