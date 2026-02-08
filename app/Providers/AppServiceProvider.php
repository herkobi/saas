<?php

namespace App\Providers;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Contracts\Shared\NotificationServiceInterface;
use App\Contracts\Shared\StorageServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Contracts\Shared\UserAnonymizationServiceInterface;
use App\Enums\ProrationType;
use App\Services\Shared\ActivityService;
use App\Services\Shared\NotificationService;
use App\Services\Shared\StorageService;
use App\Services\Shared\TenantContextService;
use App\Services\Shared\UserAnonymizationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerPanelProviders();
        $this->registerTenantProviders();
        $this->sharedProviders();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->validateHerkobiConfig();
    }

    /**
     * Register panel service providers.
     *
     * @return void
     */
    private function registerPanelProviders(): void
    {
        $this->app->register(\App\Providers\Panel\PaymentServiceProvider::class);
        $this->app->register(\App\Providers\Panel\PlanServiceProvider::class);
        $this->app->register(\App\Providers\Panel\ProfileServiceProvider::class);
        $this->app->register(\App\Providers\Panel\SettingsServiceProvider::class);
        $this->app->register(\App\Providers\Panel\SubscriptionServiceProvider::class);
        $this->app->register(\App\Providers\Panel\TenantServiceProvider::class);
    }

    /**
     * Register tenant service providers.
     *
     * @return void
     */
    private function registerTenantProviders(): void
    {
        $this->app->register(\App\Providers\App\AccountServiceProvider::class);
        $this->app->register(\App\Providers\App\ProfileServiceProvider::class);
    }

    /**
     * Register tenant service providers.
     *
     * @return void
     */
    private function validateHerkobiConfig(): void
    {
        $validProrationTypes = array_column(ProrationType::cases(), 'value');

        $upgrade = config('herkobi.proration.upgrade_behavior');
        $downgrade = config('herkobi.proration.downgrade_behavior');

        if (!in_array($upgrade, $validProrationTypes, true)) {
            throw new \RuntimeException(
                "Invalid herkobi.proration.upgrade_behavior: '{$upgrade}'. Valid: " . implode(', ', $validProrationTypes)
            );
        }

        if (!in_array($downgrade, $validProrationTypes, true)) {
            throw new \RuntimeException(
                "Invalid herkobi.proration.downgrade_behavior: '{$downgrade}'. Valid: " . implode(', ', $validProrationTypes)
            );
        }
    }

    private function sharedProviders(): void
    {
        $this->app->bind(ActivityServiceInterface::class, ActivityService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(StorageServiceInterface::class, StorageService::class);
        $this->app->bind(TenantContextServiceInterface::class, TenantContextService::class);
        $this->app->bind(UserAnonymizationServiceInterface::class, UserAnonymizationService::class);
    }
}
