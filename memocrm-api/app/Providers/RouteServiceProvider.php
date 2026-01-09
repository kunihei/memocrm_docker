<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        Route::macro('registerProtected', function (string $prefix, string $controller, array $reads = [], array $writes = []) {
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
        });
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
