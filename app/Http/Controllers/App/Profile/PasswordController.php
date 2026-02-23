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

use App\Services\App\Profile\PasswordService;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Profile\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return Inertia::render('app/Profile/Password');
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
