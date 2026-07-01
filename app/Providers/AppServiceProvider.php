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

        // View Composer untuk mendeteksi Ulang Tahun secara global di layouts.master
        view()->composer('layouts.master', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $todayMonth = now()->month;
                $todayDay = now()->day;
                $currentYear = now()->year;

                $birthdayUsers = \App\Models\User::where('is_active', true)
                    ->whereNotNull('birth_date')
                    ->whereMonth('birth_date', $todayMonth)
                    ->whereDay('birth_date', $todayDay)
                    ->with([
                        'birthdayGreetingsReceived' => function ($query) use ($currentYear) {
                            $query->where('year', $currentYear)->with('sender')->latest();
                        },
                        'role',
                        'division'
                    ])
                    ->get();

                $view->with('birthdayUsers', $birthdayUsers);
            } else {
                $view->with('birthdayUsers', collect());
            }
        });
    }
}
