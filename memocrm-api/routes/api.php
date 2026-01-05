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

Route::middleware(['auth:sanctum'])->group(function () {
    $registerProtected = function (string $prefix, string $controller, array $reads = [], array $writes = []) {
        Route::prefix($prefix)->controller($controller)->group(function () use ($reads, $writes) {
            if (!empty($reads)) {
                Route::middleware('throttle:api-reads')->group(function () use ($reads) {
                    foreach ($reads as $uri => $action) {
                        Route::get($uri, $action);
                    }
                });
            }
            if (!empty($writes)) {
                Route::middleware('throttle:api-writes')->group(function () use ($writes) {
                    foreach ($writes as $uri => $action) {
                        Route::post($uri, $action);
                    }
                });
            }
        });
    };
    $registerProtected(
        'customers',
        CustomersController::class,
        [
            'list' => 'list'
        ],
        [
            'regist' => 'regist',
            'update' => 'update',
            'delete' => 'delete'
        ]
    );
});
