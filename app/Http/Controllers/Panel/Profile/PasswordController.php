<?php

/**
 * Panel Password Controller
 *
 * This controller handles password management operations for panel users
 * including displaying the password edit form and processing password updates.
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

use App\Services\Panel\Profile\PasswordService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Profile\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel user password management.
 *
 * Provides functionality for panel users to view and update
 * their account password with proper validation.
 */
class PasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PasswordService $passwordService Service for password operations
     */
    public function __construct(
        private readonly PasswordService $passwordService
    ) {}

    /**
     * Show the user's password edit page.
     *
     * @return Response
     */
    public function edit(): Response
    {
        return Inertia::render('panel/Profile/Password');
    }

    /**
     * Update the user's password.
     *
     * @param UpdatePasswordRequest $request The validated password update request
     * @return RedirectResponse
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->passwordService->update(
            $request->user(),
            $request->validated('password'),
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'unknown'
        );

        return back()->with('success', 'Şifreniz güncellendi.');
    }
}
