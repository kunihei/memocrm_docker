<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/health', fn () => ['ok' => true]);
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
