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
            // Read the real host from Render's forwarded headers.
            $host = $_SERVER['HTTP_X_FORWARDED_HOST']
                 ?? $_SERVER['HTTP_HOST']
                 ?? parse_url(config('app.url'), PHP_URL_HOST)
                 ?? null;

            if ($host) {
                $baseUrl = 'https://' . $host;

                // Force the URL generator (affects route(), url())
                \URL::forceScheme('https');
                \URL::forceRootUrl($baseUrl);

                // Also override configs so asset() and Livewire use HTTPS
                // asset() reads app.url, Livewire reads livewire.asset_url
                config(['app.url'           => $baseUrl]);
                config(['app.asset_url'     => $baseUrl]);
                config(['livewire.asset_url' => $baseUrl]);
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
