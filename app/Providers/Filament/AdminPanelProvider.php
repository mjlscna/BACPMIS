<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ProcurementResource\Pages\BulkEditProcurements;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
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
            ->brandName('BAC Procurement Monitoring System')
            ->id('admin')
            ->path('admin-panel')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')

            ->navigation(
                fn(NavigationBuilder $builder) => $builder
                    ->items($this->getNavigationItems())
                    ->groups($this->getNavigationGroups())
            )

            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                BulkEditProcurements::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware($this->getMiddlewares())
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarFullyCollapsibleOnDesktop();
        ;
    }

    /**
     * ✅ Build the main navigation items.
     */
    protected function getNavigationItems(): array
    {
        return [
            // ✅ Dashboard always first
            // NavigationItem::make('Dashboard')
            //     ->icon('heroicon-o-home')
            //     ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.dashboard'))
            //     ->url(fn() => route('filament.admin.pages.dashboard')),

            // ✅ Automatically add resources **without a group** after the Dashboard
            // ...collect(Filament::getResources())
            //     ->reject(fn($resource) => in_array($resource, $this->getAllGroupedResources())) // Exclude grouped resources
            //     ->map(fn($resource) => $resource::getNavigationItems())
            //     ->flatten(1)
            //     ->all(),
        ];
    }

    /**
     * ✅ Get the navigation groups.
     */
    protected function getNavigationGroups(): array
    {
        return [
            // ✅ System Management Group (Auto-grouped)
            NavigationGroup::make('⚙️ System Management')
                ->collapsed()
                ->items(
                    collect($this->getSystemManagementResources())
                        ->map(fn($resource) => $resource::getNavigationItems())
                        ->flatten(1)
                        ->all()
                ),

            // ✅ Settings Group (Auto-grouped)
            NavigationGroup::make('⚙️ Settings')
                ->collapsed()
                ->items(
                    collect($this->getSettingsResources())
                        ->map(fn($resource) => $resource::getNavigationItems())
                        ->flatten(1)
                        ->all()
                ),
        ];
    }

    /**
     * ✅ Define System Management resources.
     */
    protected function getSystemManagementResources(): array
    {
        return [
            \App\Filament\Resources\CategoryResource::class,
            \App\Filament\Resources\CategoryTypeResource::class,
            \App\Filament\Resources\ClusterCommitteeResource::class,
            \App\Filament\Resources\DivisionResource::class,
            \App\Filament\Resources\FundSourceResource::class,
            \App\Filament\Resources\ModeOfProcurementResource::class,
            \App\Filament\Resources\ProcurementStageResource::class,
            \App\Filament\Resources\ProvinceResource::class,
            \App\Filament\Resources\RemarksResource::class,
            \App\Filament\Resources\EndUserResource::class,
            \App\Filament\Resources\VenueResource::class,
            \App\Filament\Resources\FundClassResource::class,
            \App\Filament\Resources\SupplierResource::class,
        ];
    }

    /**
     * ✅ Define Settings resources.
     */
    protected function getSettingsResources(): array
    {
        return [
            \App\Filament\Resources\UserResource::class,
        ];
    }

    /**
     * ✅ Get all grouped resources (to exclude from main navigation).
     */
    protected function getAllGroupedResources(): array
    {
        return array_merge(
            $this->getSystemManagementResources(),
            $this->getSettingsResources()
        );
    }

    /**
     * ✅ Define the global middlewares.
     */
    protected function getMiddlewares(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];
    }
}
