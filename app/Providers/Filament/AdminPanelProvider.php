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
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
        $settings = Setting::first();

        $logoLight = $settings?->logo_light_path
            ? Storage::disk('public')->url($settings->logo_light_path)
            : 'https://e-zandu.com/default/logo-light.png';

        $logoDark  = $settings?->logo_dark_path
            ? Storage::disk('public')->url($settings->logo_dark_path)
            : 'https://e-zandu.com/default/logo-dark.png';

        $favicon   = $settings?->icon_path
            ? Storage::disk('public')->url($settings->icon_path)
            : 'https://e-zandu.com/default/icon-default.png';

        $primaryColorHex = $settings?->primary_color_hex ?? '#fe9a00';

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
            ->favicon($favicon)
            ->brandLogoHeight('2.5rem')

            ->colors([
                'primary' => Color::hex($primaryColorHex),
                'danger' => [
                    50  => '#fff5f5',
                    950 => '#450a0a',
                ],
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
                \App\Http\Middleware\ReadOnlyDemo::class,
                \App\Http\Middleware\CheckAdminPermission::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                \CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin::make(),
                 \MixCode\FilamentMulti2fa\FilamentMulti2faPlugin::make()
                 ->forceSetup2fa(),
            ]);
    }

    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js'),
        ]);
    }
}
