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
// --- ADICIONADO ---
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // --- INÍCIO DA LÓGICA DINÂMICA ---
        $settings = Setting::first();

        // Obtém os caminhos (ou os defaults externos)
        $logoLight = $settings?->logo_light_path
            ? Storage::disk('public')->url($settings->logo_light_path)
            : 'https://e-zandu.com/default/logo-light.png';

        $logoDark  = $settings?->logo_dark_path
            ? Storage::disk('public')->url($settings->logo_dark_path)
            : 'https://e-zandu.com/default/logo-dark.png';

        $favicon   = $settings?->icon_path
            ? Storage::disk('public')->url($settings->icon_path)
            : 'https://e-zandu.com/default/icon-default.png';
        // --- FIM DA LÓGICA ---

        return $panel
            ->default()
            ->id('admin')
            ->unsavedChangesAlerts()
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()

            ->brandLogo($logoDark)
            ->darkModeBrandLogo($logoLight)
            // ->favicon('https://e-zandu.com/default/icon-default.png')
            ->brandLogoHeight('2.9rem')
            // --- FIM DA CORREÇÃO ---

            ->colors([
                'danger' => Color::Amber,
            ])
            ->profile(EditProfile::class)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
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
