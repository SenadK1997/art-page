<?php

namespace App\Providers;

use App\Support\Cart;
use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->scoped(Cart::class);
    }

    public function boot(): void
    {
        // Admin-editable shop settings, available in every view as $settings.
        View::composer('*', fn ($view) => $view->with('settings', app(Settings::class)));
    }
}
