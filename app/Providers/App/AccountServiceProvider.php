<?php

declare(strict_types=1);

namespace App\Providers\App;

use App\Contracts\App\TenantServiceInterface;
use App\Contracts\App\Account\AddonPurchaseServiceInterface;
use App\Contracts\App\Account\BillingServiceInterface;
use App\Contracts\App\Account\CheckoutServiceInterface;
use App\Contracts\App\Account\FeatureUsageServiceInterface;
use App\Contracts\App\Account\InvitationServiceInterface;
use App\Contracts\App\Account\PaymentGatewayInterface;
use App\Contracts\App\Account\PaymentServiceInterface;
use App\Contracts\App\Account\PlanChangeServiceInterface;
use App\Contracts\App\Account\ProrationServiceInterface;
use App\Contracts\App\Account\SubscriptionLifecycleServiceInterface;
use App\Contracts\App\Account\SubscriptionPurchaseServiceInterface;
use App\Contracts\App\Account\SubscriptionServiceInterface;
use App\Contracts\App\Account\UsageResetServiceInterface;
use App\Contracts\App\Account\UserServiceInterface;
use App\Services\App\TenantService;
use App\Services\App\Account\AddonPurchaseService;
use App\Services\App\Account\BillingService;
use App\Services\App\Account\CheckoutService;
use App\Services\App\Account\FeatureUsageService;
use App\Services\App\Account\InvitationService;
use App\Services\App\Account\PaymentService;
use App\Services\App\Account\PayTRService;
use App\Services\App\Account\PlanChangeService;
use App\Services\App\Account\ProrationService;
use App\Services\App\Account\SubscriptionLifecycleService;
use App\Services\App\Account\SubscriptionPurchaseService;
use App\Services\App\Account\SubscriptionService;
use App\Services\App\Account\UsageResetService;
use App\Services\App\Account\UserService;
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
        $this->app->bind(TenantServiceInterface::class, TenantService::class);
        $this->app->bind(AddonPurchaseServiceInterface::class, AddonPurchaseService::class);
        $this->app->bind(BillingServiceInterface::class, BillingService::class);
        $this->app->bind(CheckoutServiceInterface::class, CheckoutService::class);
        $this->app->bind(FeatureUsageServiceInterface::class, FeatureUsageService::class);
        $this->app->bind(InvitationServiceInterface::class, InvitationService::class);
        $this->app->bind(PaymentGatewayInterface::class, PayTRService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(PlanChangeServiceInterface::class, PlanChangeService::class);
        $this->app->bind(SubscriptionLifecycleServiceInterface::class, SubscriptionLifecycleService::class);
        $this->app->bind(SubscriptionPurchaseServiceInterface::class, SubscriptionPurchaseService::class);
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);
        $this->app->bind(ProrationServiceInterface::class, ProrationService::class);
        $this->app->bind(UsageResetServiceInterface::class, UsageResetService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }
}
