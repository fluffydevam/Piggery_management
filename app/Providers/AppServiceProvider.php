<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // The production deployment is HTTPS-only (Vercel + HSTS). Forcing the
        // scheme keeps generated URLs correct even if a proxy header is lost.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
