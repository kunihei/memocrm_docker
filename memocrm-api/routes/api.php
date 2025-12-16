<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/health', fn () => ['ok' => true]);
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/me', fn() => request()->user());
// });
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
