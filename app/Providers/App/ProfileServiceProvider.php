<?php

declare(strict_types=1);

namespace App\Providers\App;

use App\Contracts\App\Profile\PasswordServiceInterface;
use App\Contracts\App\Profile\ProfileServiceInterface;
use App\Contracts\App\Profile\TwoFactorServiceInterface;
use App\Services\App\Profile\PasswordService;
use App\Services\App\Profile\ProfileService;
use App\Services\App\Profile\TwoFactorService;
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
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(PasswordServiceInterface::class, PasswordService::class);
        $this->app->bind(TwoFactorServiceInterface::class, TwoFactorService::class);
    }
}
