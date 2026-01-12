<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force HTTPS when behind reverse proxy (NPM) or in production
        if (env('FORCE_HTTPS', false) || env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }
    }
}
