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
        // Painting and customer photos may be up to 15 MB (Livewire's default is 12 MB).
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:20480']]);

        // Admin-editable shop settings, available in every view as $settings.
        View::composer('*', fn ($view) => $view->with('settings', app(Settings::class)));
    }
}
