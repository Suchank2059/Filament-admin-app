<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
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
            ->colors([
                'primary' => 'rgb(29, 78, 216)',
            ])
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            // ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Setting')
                    ->url('')
                    ->icon('heroicon-o-cog-6-tooth'),

                'logout' => MenuItem::make()->label('Log Out')
            ])
            // ->breadcrumbs(false)
            ->navigationItems([
                NavigationItem::make('Facebook')
                    ->url('https://www.facebook.com/profile.php?id=100089595583143', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-user-circle')
                    ->group('Find me in')
                    ->sort(2),
                NavigationItem::make('Linked In')
                    ->url('https://www.linkedin.com/in/suchank-bhattarai-14b5742a4/', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-user-circle')
                    ->group('Find me in')
                    ->sort(3),
            ])
            ->font('https://fonts.googleapis.com/css2?family=Merriweather:wght@300&family=Roboto+Slab:wght@300&display=swap')
            ->favicon('images/favicon.svg')
            // ->darkMode(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
