<?php

declare(strict_types=1);

namespace App\Providers\App;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for tenant authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the tenant context.
 */
class ProfileServiceProvider extends ServiceProvider
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
        //
    }
}
