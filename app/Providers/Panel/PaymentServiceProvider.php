<?php

declare(strict_types=1);

namespace App\Providers\Panel;

use App\Contracts\Panel\Payment\PaymentServiceInterface;
use App\Services\Panel\Payment\PaymentService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for panel authentication services.
 *
 * This provider binds authentication service interfaces to their concrete
 * implementations for the panel context.
 */
class PaymentServiceProvider extends ServiceProvider
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
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
    }
}
