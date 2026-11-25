<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ShopOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Foco Art · Admin')
            ->brandLogo(fn () => view('filament.brand'))
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#c9a15e'),
                'gray' => Color::Stone,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigationItems([
                NavigationItem::make('Kalkulator')
                    ->url('/kalkulator', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedCalculator)
                    ->sort(98),
                NavigationItem::make('Otvori stranicu')
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                ShopOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
