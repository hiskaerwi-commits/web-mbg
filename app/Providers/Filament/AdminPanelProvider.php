<?php

namespace App\Providers\Filament;

use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('institution')
            ->path(config('admin.path'))
            ->login(\App\Filament\Auth\Login::class)
            ->profile(\App\Filament\Auth\EditProfile::class, isSimple: false)
            ->brandName(fn (): string => SiteSetting::current()->site_name)
            ->brandLogo(fn (): ?string => ($path = SiteSetting::current()->logo_path) ? Storage::disk('public')->url($path) : null)
            ->brandLogoHeight('2.5rem')
            ->favicon(fn (): ?string => ($path = SiteSetting::current()->favicon_path) ? Storage::disk('public')->url($path) : null)
            ->colors([
                'primary' => Color::hex('#071E49'),
                'success' => Color::hex('#5E9E33'),
                'gray' => Color::Slate,
            ])
            ->darkMode(false)
            ->themeSwitcher(false)
            ->viteTheme('resources/css/filament/institution/theme.css')
            ->sidebarWidth('17rem')
            ->maxContentWidth(Width::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
