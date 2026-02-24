<?php

/**
 * Panel Routes
 *
 * Routes for panel (admin) users with middleware protection.
 * Account routes allow DRAFT users, functional routes block write operations.
 *
 * @package    Routes
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\Payment\PaymentController;
use App\Http\Controllers\Panel\Subscription\SubscriptionController;
use App\Http\Controllers\Panel\Addon\AddonController;
use App\Http\Controllers\Panel\Plan\{
    FeatureController,
    PlanController,
    PlanFeatureController,
    PlanPriceController
};
use App\Http\Controllers\Panel\Profile\{
    AppearanceController,
    NotificationController,
    PasswordController,
    ProfileController,
    TwoFactorAuthenticationController
};
use App\Http\Controllers\Panel\Settings\{
    SettingController
};
use App\Http\Controllers\Panel\Tenant\{
    TenantActivityController,
    TenantAddonController,
    TenantController,
    TenantFeatureController,
    TenantPaymentController,
    TenantSubscriptionController,
    TenantUserController
};
use App\Http\Controllers\Panel\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth.session', 'verified'])->group(function () {

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
});

/*
|--------------------------------------------------------------------------
| Functional Routes (DRAFT users blocked from write operations)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth.session', 'verified', 'write.access'])->group(function () {

    /**
     * Dashboard Routes
     */
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'index')->name('dashboard');
    });

    /**
     * Payment Routes
     */
    Route::prefix('payments')->name('payments.')->controller(PaymentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('upcoming', 'upcoming')->name('upcoming');

        Route::get('statistics', 'statistics')->name('statistics');
        Route::get('revenue-chart', 'revenueChart')->name('revenue-chart');

        Route::get('{payment}', 'show')->name('show');
        Route::put('{payment}/status', 'updateStatus')->name('update-status');
        Route::post('{payment}/mark-as-invoiced', 'markAsInvoiced')->name('mark-as-invoiced');
        Route::post('mark-many-as-invoiced', 'markManyAsInvoiced')->name('mark-many-as-invoiced');
    });

    /**
     * Subscription Routes
     */
    Route::prefix('subscriptions')->name('subscriptions.')->controller(SubscriptionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{subscription}', 'show')->name('show');
    });

    /**
     * Users Routes
     */
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });

    /**
     * Tenants Routes
     */
    Route::prefix('tenants')->name('tenants.')->group(function () {

        Route::controller(TenantController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{tenant}', 'show')->name('show');
            Route::put('{tenant}', 'update')->name('update');
        });

        /**
         * Tenant Subscription Routes
         */
        Route::prefix('{tenant}/subscription')->name('subscription.')->controller(TenantSubscriptionController::class)->group(function () {
            Route::get('/', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::post('cancel', 'cancel')->name('cancel');
            Route::post('renew', 'renew')->name('renew');
            Route::post('change-plan', 'changePlan')->name('change-plan');
            Route::post('extend-trial', 'extendTrial')->name('extend-trial');
            Route::post('extend-grace-period', 'extendGracePeriod')->name('extend-grace-period');
            Route::put('status', 'updateStatus')->name('update-status');
            Route::put('custom-price', 'updateCustomPrice')->name('update-custom-price');
        });

        /**
         * Tenant Users Routes
         */
        Route::prefix('{tenant}/users')->name('users.')->controller(TenantUserController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{user}/status', 'updateStatus')->name('update-status');
        });

        /**
         * Tenant Payments Routes
         */
        Route::prefix('{tenant}/payments')->name('payments.')->controller(TenantPaymentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });

        /**
         * Tenant Addons Routes
         */
        Route::prefix('{tenant}/addons')->name('addons.')->controller(TenantAddonController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('{addon}/extend', 'extend')->name('extend');
            Route::post('{addon}/cancel', 'cancel')->name('cancel');
        });

        /**
         * Tenant Features Routes
         */
        Route::prefix('{tenant}/features')->name('features.')->controller(TenantFeatureController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'sync')->name('sync');
            Route::delete('{feature}', 'destroy')->name('destroy');
            Route::post('clear', 'clear')->name('clear');
        });

        /**
         * Tenant Activities Routes
         */
        Route::prefix('{tenant}/activities')->name('activities.')->controller(TenantActivityController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });
    });

    /**
     * Settings Routes
     */
    Route::prefix('settings')->name('settings.')->group(function () {

        /**
         * General Settings Routes
         */
        Route::prefix('general')->name('general.')->controller(SettingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
            Route::delete('/file/{key}', 'deleteFile')->name('delete-file');
        });

        Route::prefix('company')->name('company.')->controller(SettingController::class)->group(function () {
            Route::get('/', 'companyIndex')->name('index');
            Route::put('/', 'updateCompany')->name('update');
        });
    });

    /**
     * Plans Routes
     */
    Route::prefix('plans')->name('plans.')->group(function () {

        /**
         * Plans Routes
         */
        Route::controller(PlanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('{plan}/edit', 'edit')->name('edit');
            Route::put('{plan}', 'update')->name('update');

            Route::post('{plan}/publish', 'publish')->name('publish');
            Route::post('{plan}/unpublish', 'unpublish')->name('unpublish');
            Route::post('{plan}/archive', 'archive')->name('archive');
            Route::post('{plan}/restore', 'restore')->name('restore');
        });

        /**
         * Features Routes
         */
        Route::prefix('features')->name('features.')->controller(FeatureController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('{feature}/edit', 'edit')->name('edit');
            Route::put('{feature}', 'update')->name('update');

            Route::delete('{feature}', 'destroy')->name('destroy');
        });

        /**
         * Addons Routes
         */
        Route::prefix('addons')->name('addons.')->controller(AddonController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('{addon}/edit', 'edit')->name('edit');
            Route::put('{addon}', 'update')->name('update');
            Route::delete('{addon}', 'destroy')->name('destroy');
        });

        /**
         * Plan Features Routes
         */
        Route::prefix('{plan}/features')->name('plan-features.')->controller(PlanFeatureController::class)->group(function () {
            Route::put('/', 'sync')->name('sync');
        });

        /**
         * Plan Prices Routes
         *
         * Not: Fiyat yönetimi plan düzenle sayfasında yapılır.
         * Bu yüzden GET (listele/ekle/düzenle sayfaları) rotaları yoktur.
         */
        Route::prefix('{plan}/prices')->name('prices.')->controller(PlanPriceController::class)->group(function () {
            Route::post('/', 'store')->name('store');
            Route::put('{price}', 'update')->name('update');
            Route::delete('{price}', 'destroy')->name('destroy');
        });
    });
});
