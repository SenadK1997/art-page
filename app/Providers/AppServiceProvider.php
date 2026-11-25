<?php

namespace App\Providers;

use App\Support\Cart;
use App\Support\Settings;
use Illuminate\Support\Facades\URL;
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
        // Behind the https proxy, always build https links (assets, Livewire, signed URLs).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Upload boxes in the admin: the upload library has no Bosnian text of its own.
        \Filament\Forms\Components\FileUpload::configureUsing(fn ($upload) => $upload->placeholder(
            'Dodirnite ovdje i izaberite fotografiju <span class="filepond--label-action">(ili je prevucite)</span>'
        ));

        // Painting and customer photos may be up to 15 MB (Livewire's default is 12 MB).
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:20480']]);

        // Admin-editable shop settings, available in every view as $settings.
        View::composer('*', fn ($view) => $view->with('settings', app(Settings::class)));
    }
}
