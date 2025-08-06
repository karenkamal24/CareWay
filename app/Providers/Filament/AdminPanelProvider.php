<?php

namespace App\Providers\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
use Filament\Panel\Components\RenderHook;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Navigation\NavigationGroup;
use App\Filament\Resources\Pharmacy\CategoryResource;
use App\Filament\Resources\Pharmacy\DeliverySettingResource;
use App\Filament\Resources\Pharmacy\OrderResource;
use App\Filament\Resources\Pharmacy\ProductResource;
use App\Filament\Resources\DoctorResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\Lab\TestResultResource;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(fn () => view('filament.resources.admin.logo'))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => '#3f5979',
            ])
            // ->viteTheme(['resources/css/filament.css'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\Chart::class,
                \App\Filament\Widgets\OrdersStatsOverview::class,
                \App\Filament\Widgets\orderstuts::class,
                // \Filament\Notifications\Livewire\Notifications::class,
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->authMiddleware([
                Authenticate::class,
            ])
            ->resources([
                RoleResource::class,
                UserResource::class,
                DoctorResource::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])

            // ->topNavigation()
            // ->sidebarCollapsibleOnDesktop(true);
            ->sidebarFullyCollapsibleOnDesktop()
            // ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
            //     return $builder->groups([
            //         NavigationGroup::make('إدارة المستشفى')
            //             ->items([
            //                 NavigationItem::make('الرئيسية')
            //                     ->url(fn () => Dashboard::getUrl())
            //                     ->icon('heroicon-o-home')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard')),
            //                 NavigationItem::make('المستخدمين')
            //                     ->url(fn () => UserResource::getUrl('index'))
            //                     ->icon('heroicon-o-users')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.users.*')),
            //                 NavigationItem::make('الأدوار')
            //                     ->url(fn () => RoleResource::getUrl('index'))
            //                     ->icon('heroicon-o-shield-check')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.roles.*')),
            //                 NavigationItem::make('الأقسام')
            //                     ->url(fn () => DepartmentResource::getUrl('index'))
            //                     ->icon('heroicon-o-folder')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.departments.*')),
            //             ]),
            //         NavigationGroup::make('الأطباء')
            //             ->items([
            //                 NavigationItem::make('الأطباء')
            //                     ->url(fn () => DoctorResource::getUrl('index'))
            //                     ->icon('heroicon-o-user-group')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.doctors.*')),
            //                 NavigationItem::make('المواعيد')
            //                     ->url(fn () => AppointmentResource::getUrl('index'))
            //                     ->icon('heroicon-o-calendar')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.appointments.*')),
            //             ]),
            //             NavigationGroup::make('المعمل')
            //             ->items([
            //                 NavigationItem::make('الفحوصات')
            //                     ->url(fn () => TestResultResource::getUrl('index'))
            //                     ->icon('heroicon-o-beaker')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.lab-tests.*')),
            //             ]),
            //         NavigationGroup::make('الصيدلية')
            //             ->items([
            //                 NavigationItem::make('الفئات')
            //                     ->url(fn () => CategoryResource::getUrl('index'))
            //                     ->icon('heroicon-o-rectangle-stack')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.categories.*')),
            //                 NavigationItem::make('إعدادات التوصيل')
            //                     ->url(fn () => DeliverySettingResource::getUrl('index'))
            //                     ->icon('heroicon-o-truck')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.delivery-settings.*')),
            //                 NavigationItem::make('الطلبات النقدية')
            //                     ->url(fn () => OrderResource::getUrl('index'))
            //                     ->icon('heroicon-o-shopping-cart')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.orders.*')),
            //                     NavigationItem::make('المنتجات')
            //                     ->url(fn () => ProductResource::getUrl('index'))
            //                     ->icon('heroicon-o-rectangle-stack')
            //                     ->badge(fn () => \App\Models\Product::where('quantity', '<=', 10)->count(), color: 'danger')
            //                     ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.products.*')),
            //             ]),

            //     ]);
            // });
            ->renderHook(
                'body.end',
                fn (): string => view('livewire.notifications-listener')->render()
            ); // ← هذه أهم خطوة

    }
}
