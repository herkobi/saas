<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Profile\PasswordServiceInterface;
use App\Contracts\Panel\Profile\ProfileServiceInterface;
use App\Contracts\Panel\Profile\TwoFactorServiceInterface;
use App\Services\Panel\Profile\PasswordService;
use App\Services\Panel\Profile\ProfileService;
use App\Services\Panel\Profile\TwoFactorService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
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
