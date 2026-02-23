<?php

namespace App\Providers;

use App\Enums\ProrationType;
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
}
