<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Use Bootstrap 4 pagination views
        Paginator::useBootstrapFour();

        // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);
    }
}
