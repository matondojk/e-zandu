<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\LatestPendingOrders;
use App\Filament\Pages\IntelligentAnalysis;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use App\Models\User;
use App\Http\Middleware\CheckSubscriptionStatus;
use App\Filament\Pages\AnalyticsDashboard;
use AchyutN\FilamentLogViewer\FilamentLogViewer;
use CWSPS154\UsersRolesPermissions\UsersRolesPermissionsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->unsavedChangesAlerts()
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->colors([
                'primary' => [
                    '50' => '#fffbeb',
                    '100' => '#fef3c7',
                    '200' => '#fde68a',
                    '300' => '#fcd34d',
                    '400' => '#f0b91a',
                    '500' => '#e59d02', // <-- A SUA COR EXATA "GOLD"
                    '600' => '#d97706', // (Os tons à volta são ajustados)
                    '700' => '#b45309',
                    '800' => '#92400e',
                    '900' => '#78350f',
                    '950' => '#451a03',
                ],
            ])
            ->profile(EditProfile::class)
            // ->brandLogo('https://matondojoao.com/wp-content/uploads/2025/10/tanga-wa-icon-2.png')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // ->favicon('https://matondojoao.com/wp-content/uploads/2025/10/tanga-wa-icon-2.png')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
                AnalyticsDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])

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
                \App\Http\Middleware\CheckTenantSubscription::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                \CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin::make(),
                // FilamentLogViewer::make()->navigationGroup('Configurações')->navigationLabel('Logs do Sistema'),
                // \MixCode\FilamentMulti2fa\FilamentMulti2faPlugin::make(),
                // ->forceSetup2fa(),
            ]);
    }

    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js'),
        ]);
    }
}
