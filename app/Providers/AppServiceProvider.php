<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        if (config('app.env') === 'production') {
            // Detect the real hostname from Render's forwarded headers.
            // This is bulletproof regardless of how APP_URL is configured.
            $host = $_SERVER['HTTP_X_FORWARDED_HOST']
                 ?? $_SERVER['HTTP_HOST']
                 ?? parse_url(config('app.url'), PHP_URL_HOST)
                 ?? null;

            if ($host) {
                \URL::forceScheme('https');
                \URL::forceRootUrl('https://' . $host);
            } else {
                \URL::forceScheme('https');
            }
        }

        View::composer(
            'includes.topbar',
            \App\View\Composers\TopbarComposer::class
        );

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('signup', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('buddy-chat', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
