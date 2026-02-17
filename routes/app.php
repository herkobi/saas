<?php

/**
 * Tenant Routes
 *
 * Routes for tenant users with multi-level middleware protection.
 * Account routes allow DRAFT users, functional routes block write operations.
 *
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 *
 * @version    1.0.0
 *
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Http\Controllers\App\Account\AddonController;
use App\Http\Controllers\App\Account\BillingController;
use App\Http\Controllers\App\Account\CheckoutController;
use App\Http\Controllers\App\Account\FeatureUsageController;
use App\Http\Controllers\App\Account\InvitationController;
use App\Http\Controllers\App\Account\PaymentCallbackController;
use App\Http\Controllers\App\Account\PaymentController;
use App\Http\Controllers\App\Account\PlanChangeController;
use App\Http\Controllers\App\Account\SubscriptionController;
use App\Http\Controllers\App\Account\UserActivityController;
use App\Http\Controllers\App\Account\UserController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\InvitationAcceptController;
use App\Http\Controllers\App\TenantController;
use App\Http\Controllers\App\Profile\AppearanceController;
use App\Http\Controllers\App\Profile\NotificationController;
use App\Http\Controllers\App\Profile\PasswordController;
use App\Http\Controllers\App\Profile\ProfileController;
use App\Http\Controllers\App\Profile\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Callback Routes (No Auth - Webhook)
|--------------------------------------------------------------------------
*/

Route::post('payment/callback', [PaymentCallbackController::class, 'handle'])
    ->middleware(['throttle:60,1'])
    ->name('payment.callback');

/*
|--------------------------------------------------------------------------
| Invitation Accept Routes (Public / Auth Required for POST)
|--------------------------------------------------------------------------
*/

Route::controller(InvitationAcceptController::class)->group(function () {
    Route::get('invitation/accept/{token}', 'show')
        ->middleware(['throttle:10,1'])
        ->name('invitation.accept');
    Route::post('invitation/accept/{token}', 'accept')
        ->middleware(['auth', 'throttle:10,1'])
        ->name('invitation.accept.process');
});

/*
|--------------------------------------------------------------------------
| Tenant Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth.session', 'verified'])->group(function () {

    /**
     * Root Redirect
     */
    Route::redirect('/', 'dashboard');

    /**
     * Dashboard Routes
     */
    Route::controller(DashboardController::class)->middleware(['tenant.member_active'])->group(function () {
        Route::get('dashboard', 'index')->name('dashboard');
    });

    /**
     * Tenant Management Routes
     */
    Route::controller(TenantController::class)->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('create', 'create')->middleware(['write.access'])->name('create');
        Route::post('/', 'store')->middleware(['write.access'])->name('store');
        Route::post('switch', 'switch')->name('switch');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    |
    | /profile
    | /profile/password
    | /profile/two-factor
    | /profile/notifications
    |
    */

    Route::prefix('profile')->name('profile.')->group(function () {

        /**
         * Profile Routes
         */
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->middleware(['write.access'])->name('update');
        });

        /**
         * Password Routes
         */
        Route::controller(PasswordController::class)->group(function () {
            Route::get('/password', 'edit')->name('password.edit');
            Route::middleware(['throttle:6,1', 'write.access'])->put('/password', 'update')->name('password.update');
        });

        /**
         * Two Factor Routes
         */
        Route::controller(TwoFactorAuthenticationController::class)->group(function () {
            Route::get('/two-factor', 'show')->name('two-factor.show');
        });

        /**
         * Appearance Routes
         */
        Route::controller(AppearanceController::class)->group(function () {
            Route::get('/appearance', 'show')->name('appearance.show');
        });

        /**
         * Notification Routes
         */
        Route::controller(NotificationController::class)->group(function () {
            Route::get('/notifications', 'index')->name('notifications.index');
            Route::get('/notifications/archived', 'archived')->name('notifications.archived');
            Route::post('/notifications/mark-as-read', 'markAsRead')->middleware(['write.access'])->name('notifications.mark-as-read');
            Route::post('/notifications/mark-all-as-read', 'markAllAsRead')->middleware(['write.access'])->name('notifications.mark-all-as-read');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Account Routes
    |--------------------------------------------------------------------------
    |
    | /account/subscription
    | /account/billing
    | /account/payments
    | /account/checkout
    | /account/addons
    | /account/plan-change
    | /account/features
    | /account/users
    |
    */

    Route::prefix('account')->name('account.')->middleware(['tenant.owner', 'tenant.member_active'])->group(function () {

        /**
         * Subscription Routes
         */
        Route::controller(SubscriptionController::class)->group(function () {
            Route::get('subscription', 'index')->middleware(['subscription.active'])->name('subscription.index');
            Route::post('subscription/cancel', 'cancel')->middleware(['subscription.active', 'write.access'])->name('subscription.cancel');
        });

        /**
         * Billing Routes
         */
        Route::controller(BillingController::class)->group(function () {
            Route::get('billing', 'index')->middleware(['subscription.active'])->name('billing.index');
            Route::put('billing', 'update')->middleware(['subscription.active', 'write.access'])->name('billing.update');
        });

        /**
         * Payment Routes
         */
        Route::controller(PaymentController::class)->group(function () {
            Route::get('payments', 'index')->middleware(['subscription.active'])->name('payments.index');
            Route::get('payments/{paymentId}', 'show')->middleware(['subscription.active'])->name('payments.show');
        });

        /**
         * Checkout Routes
         */
        Route::controller(CheckoutController::class)->group(function () {
            Route::get('checkout', 'index')->middleware(['subscription.active'])->name('checkout.index');
            Route::post('checkout', 'initiate')->middleware(['subscription.active', 'write.access'])->name('checkout.initiate');
            Route::get('checkout/{checkoutId}/processing', 'processing')->middleware(['subscription.active'])->name('checkout.processing');
            Route::get('checkout/success', 'success')->name('checkout.success');
            Route::get('checkout/failed', 'failed')->name('checkout.failed');
        });

        /**
         * Addon Routes
         */
        Route::prefix('addons')->name('addons.')->controller(AddonController::class)->group(function () {
            Route::get('/', 'index')->middleware(['subscription.active'])->name('index');
            Route::post('purchase', 'purchase')->middleware(['subscription.active', 'write.access'])->name('purchase');
            Route::post('{addonId}/cancel', 'cancel')->middleware(['subscription.active', 'write.access'])->name('cancel');
        });

        /**
         * Plan Change Routes
         */
        Route::controller(PlanChangeController::class)->group(function () {
            Route::get('plan-change', 'index')->middleware(['subscription.active'])->name('plans.index');
            Route::post('plan-change', 'change')->middleware(['subscription.active', 'write.access'])->name('plans.change');
            Route::post('plan-change/downgrade/cancel', 'cancelDowngrade')->middleware(['subscription.active', 'write.access'])->name('downgrade.cancel');
        });

        /**
         * User Management Routes
         */
        Route::prefix('users')->name('users.')->middleware(['tenant.allow_team_members', 'feature.access:users'])->group(function () {

            Route::controller(UserController::class)->group(function () {
                Route::get('/', 'index')->middleware(['subscription.active'])->name('index');
                Route::get('{userId}', 'show')->middleware(['subscription.active'])->name('show');
                Route::put('{userId}/role', 'updateRole')->middleware(['subscription.active', 'write.access'])->name('role.update');
                Route::put('{userId}/status', 'updateStatus')->middleware(['subscription.active', 'write.access'])->name('status.update');
                Route::delete('{userId}', 'remove')->middleware(['subscription.active', 'write.access'])->name('remove');
            });

            Route::get('{userId}/activities', [UserActivityController::class, 'index'])
                ->middleware(['subscription.active'])
                ->name('activities');

            /**
             * Invitation Routes
             */
            Route::prefix('invitations')->name('invitations.')->controller(InvitationController::class)->group(function () {
                Route::get('/', 'index')->middleware(['subscription.active'])->name('index');
                Route::post('/', 'invite')->middleware(['subscription.active', 'write.access'])->name('store');
                Route::delete('{invitationId}', 'revoke')->middleware(['subscription.active', 'write.access'])->name('revoke');
                Route::post('{invitationId}/resend', 'resend')->middleware(['subscription.active', 'write.access'])->name('resend');
            });
        });

        /**
         * Feature Usage Routes (API)
         */
        Route::prefix('features')->name('features.')->controller(FeatureUsageController::class)->group(function () {
            Route::get('/', 'index')->middleware(['subscription.active'])->name('index');
            Route::get('{featureSlug}', 'show')->middleware(['subscription.active'])->name('show');
            Route::get('{featureSlug}/check', 'check')->middleware(['subscription.active'])->name('check');
        });
    });
});
