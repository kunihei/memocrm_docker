<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            // email が無い/空ならIP単位にフォールバック（バリデーション前の攻撃対策）
            $key = $email !== '' ? $email.'|'.$ip : 'no-email|'.$ip;
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('refresh', function (Request $request) {
            $ip = (string) $request->ip();
            $rt = (string) $request->input('refresh_token', '');

            $rtKey = $rt !== '' ? substr(hash('sha256', $rt), 0, 32) : 'no-rt';
            return Limit::perMinute(10)->by($rtKey);
        });
    }
}
