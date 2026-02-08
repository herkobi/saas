<?php

/**
 * Tenant Password Controller
 *
 * This controller handles password management operations for tenant users
 * including displaying the password edit form and processing password updates.
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

use App\Contracts\App\Profile\PasswordServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant user password management.
 *
 * Provides functionality for tenant users to view and update
 * their account password with proper validation.
 */
class PasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PasswordServiceInterface $passwordService Service for password operations
     */
    public function __construct(
        private readonly PasswordServiceInterface $passwordService
    ) {}

    /**
     * Show the user's password edit page.
     *
     * @return Response
     */
    public function edit(): Response
    {
        return Inertia::render('app/Profile/Password');
    }

    /**
     * Update the user's password.
     *
     * @param Request $request The incoming request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $this->passwordService->update(
            $request->user(),
            $validated['password'],
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'unknown'
        );

        return back()->with('success', 'Şifreniz güncellendi.');
    }
}
