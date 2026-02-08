<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Subscription\SubscriptionServiceInterface;
use App\Services\Panel\Subscription\SubscriptionService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel subscription services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
 */
class SubscriptionServiceProvider extends ServiceProvider
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
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);
    }
}
