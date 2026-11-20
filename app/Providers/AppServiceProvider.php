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
        // Default language (the header switch / each person's preference can change it)
        $locale = config('app.default_language');
        if (array_key_exists($locale, config('app.languages'))) {
            app()->setLocale($locale);
            \Carbon\Carbon::setLocale($locale);
        }
    }
}
