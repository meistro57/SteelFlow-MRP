<?php

// AdminPanelProvider.php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->brandName('SteelFlow MRP')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
            ])
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->navigationGroups([
                'Maintenance',
                'Production',
                'Procurement',
                'Inventory',
                'Projects',
                'Reports',
                'System',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Log Viewer')
                    ->url('/admin/logs')
                    ->icon('heroicon-o-document-text')
                    ->group('System')
                    ->sort(99)
                    ->visible(fn (): bool => auth()->user()?->role && in_array(auth()->user()->role, ['admin', 'manager', 'supervisor'])),
                NavigationItem::make('Telescope')
                    ->url('/telescope')
                    ->icon('heroicon-o-sparkles')
                    ->group('System')
                    ->sort(100)
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() && app()->environment('local')),
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
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
