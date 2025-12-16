<?php
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true]);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn() => request()->user());
});