<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Tenant\TenantServiceInterface;
use App\Contracts\Panel\Tenant\TenantFeatureServiceInterface;
use App\Contracts\Panel\Tenant\TenantSubscriptionServiceInterface;
use App\Services\Panel\Tenant\TenantService;
use App\Services\Panel\Tenant\TenantFeatureService;
use App\Services\Panel\Tenant\TenantSubscriptionService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
 */
class TenantServiceProvider extends ServiceProvider
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
        $this->app->bind(TenantServiceInterface::class, TenantService::class);
        $this->app->bind(TenantFeatureServiceInterface::class, TenantFeatureService::class);
        $this->app->bind(TenantSubscriptionServiceInterface::class, TenantSubscriptionService::class);
    }
}
