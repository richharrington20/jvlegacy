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
        // Register email logging event listener
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSent::class,
            \App\Listeners\LogEmailSent::class
        );
    }
}
