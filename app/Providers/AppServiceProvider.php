<?php

namespace App\Providers;

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
        
    // Opšti API limiter: 60 zahteva / minut, po korisniku (ako je ulogovan) ili po IP-u
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });

    // Login limiter: 10 pokušaja / minut po IP-u (ili e-mailu/IP kombinaciji, po želji)
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(10)->by($request->ip());
    });
    }
}
