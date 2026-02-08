<?php

/**
 * Tenant Two Factor Authentication Controller
 *
 * This controller handles two-factor authentication management for tenant users
 * including displaying the 2FA settings page.
 *
 * @package    App\Http\Controllers\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Profile\TwoFactorAuthenticationRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Controller for tenant user two-factor authentication.
 *
 * Provides functionality for tenant users to manage their
 * two-factor authentication settings.
 */
class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    /**
     * Show the user's two-factor authentication account page.
     *
     * @param TwoFactorAuthenticationRequest $request
     * @return Response
     */
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        return Inertia::render('app/Profile/TwoFactor', [
            'user' => $request->user(),
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }
}
