<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // Otomatis pakai HTTPS jika bukan di localhost atau Herd (.test)
        $host = request()->getHost();
        if (!in_array($host, ['localhost', '127.0.0.1', '::1']) && !str_ends_with($host, '.test')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
