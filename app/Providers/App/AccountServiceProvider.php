<?php

declare(strict_types=1);

namespace App\Providers\App;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for tenant account services.
 *
 * This provider binds account service interfaces to their concrete
 * implementations for the tenant context.
 */
class AccountServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
