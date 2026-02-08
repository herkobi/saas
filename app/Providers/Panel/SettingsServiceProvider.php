<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Settings\SettingServiceInterface;
use App\Services\Panel\Settings\SettingService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
 */
class SettingsServiceProvider extends ServiceProvider
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
        $this->app->bind(SettingServiceInterface::class, SettingService::class);
    }
}
