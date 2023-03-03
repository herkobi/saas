<?php

use App\Http\Controllers\Admin\Account\Profile\ProfileController;
use App\Http\Controllers\Admin\Customers\CustomersController;
use App\Http\Controllers\Admin\Payment\PaymentController;
use App\Http\Controllers\Admin\Plan\PlanController;
use App\Http\Controllers\Admin\Settings\ContractsController;
use App\Http\Controllers\Admin\Settings\EmailController;
use App\Http\Controllers\Admin\Settings\GatewayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(static function () {

    // Guest routes
    Route::middleware('guest:admin')->group(static function () {
        // Auth routes
        Route::get('login', [\App\Http\Controllers\Admin\Auth\AuthenticatedSessionController::class, 'create'])->name('admin.login');
        Route::post('login', [\App\Http\Controllers\Admin\Auth\AuthenticatedSessionController::class, 'store']);
        // Forgot password
        Route::get('forgot-password', [\App\Http\Controllers\Admin\Auth\PasswordResetLinkController::class, 'create'])->name('admin.password.request');
        Route::post('forgot-password', [\App\Http\Controllers\Admin\Auth\PasswordResetLinkController::class, 'store'])->name('admin.password.email');
        // Reset password
        Route::get('reset-password/{token}', [\App\Http\Controllers\Admin\Auth\NewPasswordController::class, 'create'])->name('admin.password.reset');
        Route::post('reset-password', [\App\Http\Controllers\Admin\Auth\NewPasswordController::class, 'store'])->name('admin.password.update');
    });

    // Verify email routes
    Route::middleware(['auth:admin'])->group(static function () {
        Route::get('verify-email', [\App\Http\Controllers\Admin\Auth\EmailVerificationPromptController::class, '__invoke'])->name('admin.verification.notice');
        Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Admin\Auth\VerifyEmailController::class, '__invoke'])->middleware(['signed', 'throttle:6,1'])->name('admin.verification.verify');
        Route::post('email/verification-notification', [\App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('admin.verification.send');
    });

    // Authenticated routes
    Route::middleware(['auth:admin', 'verified'])->group(static function () {
        // Logout route
        Route::post('logout', [\App\Http\Controllers\Admin\Auth\AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
        // General routes
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');

        Route::get('/customers', [CustomersController::class, 'index'])->name('admin.customers');

        Route::get('/plans', [PlanController::class, 'index'])->name('admin.plans');
        Route::get('/plans/free', [PlanController::class, 'free'])->name('admin.plans.free');
        Route::get('/plans/new', [PlanController::class, 'create'])->name('admin.plan.create');

        Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments');
        Route::get('/payments/open', [PaymentController::class, 'open'])->name('admin.payments.open');
        Route::get('/payments/approved', [PaymentController::class, 'approved'])->name('admin.payments.approved');
        Route::get('/payments/closed', [PaymentController::class, 'closed'])->name('admin.payments.closed');

        Route::get('/settings/gateway', [GatewayController::class, 'index'])->name('admin.settings.gateway');
        Route::get('/settings/email', [EmailController::class, 'index'])->name('admin.settings.email');
        Route::get('/settings/contracts', [ContractsController::class, 'index'])->name('admin.settings.contracts');
    });
});

