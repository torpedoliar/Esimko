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
        // Dynamic APP_URL based on request (works with any domain)
        if (request()->getHost()) {
            $scheme = request()->secure() ? 'https' : 'http';
            
            // Force HTTPS when behind reverse proxy (NPM) or in production
            if (env('FORCE_HTTPS', false) || env('APP_ENV') === 'production') {
                $scheme = 'https';
            }
            
            // Set dynamic URL
            \URL::forceScheme($scheme);
            \URL::forceRootUrl($scheme . '://' . request()->getHost());
        }
    }
}
