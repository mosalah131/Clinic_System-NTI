<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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
        // Older MySQL versions (before 5.7.7) limit index keys to 767 bytes.
        // Setting a default string length of 191 keeps "unique" columns working
        // on every XAMPP version.
        Schema::defaultStringLength(191);
    }
}
