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
        //
    }

    /**
     * Register providers.
     */
    public function registerProviders(): void
    {
        $this->app->register(\Livewire\LivewireServiceProvider::class);
        $this->app->register(\App\Providers\EventServiceProvider::class);
        $this->app->register(\App\Providers\RouteServiceProvider::class);
    }
}
