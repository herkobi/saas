<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Addon\AddonServiceInterface;
use App\Contracts\Panel\Addon\TenantAddonServiceInterface;
use App\Contracts\Panel\Plan\FeatureServiceInterface;
use App\Contracts\Panel\Plan\PlanPriceServiceInterface;
use App\Contracts\Panel\Plan\PlanServiceInterface;
use App\Services\Panel\Addon\AddonService;
use App\Services\Panel\Addon\TenantAddonService;
use App\Services\Panel\Plan\FeatureService;
use App\Services\Panel\Plan\PlanPriceService;
use App\Services\Panel\Plan\PlanService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
 */
class PlanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PlanServiceInterface::class, PlanService::class);
        $this->app->bind(FeatureServiceInterface::class, FeatureService::class);
        $this->app->bind(PlanPriceServiceInterface::class, PlanPriceService::class);
        $this->app->bind(AddonServiceInterface::class, AddonService::class);
        $this->app->bind(TenantAddonServiceInterface::class, TenantAddonService::class);
    }
}
