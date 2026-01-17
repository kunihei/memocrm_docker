<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
        /**
         * 目的:
         *   コントローラ単位で保護された（レート制限付き）ルートをまとめて登録するための
         *   `registerProtected` マクロを定義します。
         *
         * 概要:
         *   - 第1引数 `$prefix` : ルートのプレフィックス（例: 'api/customers'）
         *   - 第2引数 `$controller` : コントローラのクラス名
         *   - 第3引数 `$reads` : 読み取り用ルートの連想配列 (uri => action)
         *     例: ['list' => 'list', 'show/{co_cd}' => 'show']
         *   - 第4引数 `$writes` : 書き込み用ルートの連想配列 (uri => action)
         *
         *   このマクロは、読み取りルートに `throttle:api-reads`、
         *   書き込みルートに `throttle:api-writes` ミドルウェアを適用し、それぞれ GET / POST で登録します。
         */
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
}
