<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsurePanel;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\EnsureWriteAccess;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LoadTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('app')
                ->middleware(['web', EnsureTenant::class])
                ->name('app.')
                ->group(base_path('routes/app.php'));

            Route::prefix('panel')
                ->middleware(['web', EnsurePanel::class])
                ->name('panel.')
                ->group(base_path('routes/panel.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            HandleAppearance::class,
            LoadTenantContext::class,
            HandleInertiaRequests::class,
            EnsureActiveUser::class,
        ]);

        $middleware->alias([
            'subscription.active' => EnsureActiveSubscription::class,
            'write.access' => EnsureWriteAccess::class,
            'tenant.allow_team_members' => \App\Http\Middleware\EnsureTenantAllowsTeamMembers::class,
            'feature.access' => \App\Http\Middleware\EnsureFeatureAccess::class,
            'tenant.owner' => \App\Http\Middleware\EnsureTenantOwner::class,
            'tenant.member_active' => \App\Http\Middleware\EnsureTenantMemberActive::class,
        ]);

    })
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
