<?php

/**
 * Panel Two Factor Authentication Controller
 *
 * This controller handles two-factor authentication management for panel users
 * including displaying the 2FA settings page.
 *
 * @package    App\Http\Controllers\Panel\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Profile\TwoFactorAuthenticationRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Controller for panel user two-factor authentication.
 *
 * Provides functionality for panel users to manage their
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
     * Show the user's two-factor authentication profile page.
     *
     * @param TwoFactorAuthenticationRequest $request
     * @return Response
     */
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        // Auth::user() da $request->user() da aynı şey, ikisi de cache'lenir
        // O yüzden fresh() ile DB'den yeniden çekiyoruz
        $user = Auth::user()->fresh();

        return Inertia::render('panel/Profile/TwoFactor', [
            'user' => $user,
            'twoFactorEnabled' => !is_null($user->two_factor_secret),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }
}
