<?php

/**
 * Tenant Profile Controller
 *
 * This controller handles profile management operations for tenant users
 * including displaying the profile edit form, processing profile updates,
 * and account deletion.
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

use App\Services\App\Profile\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Profile\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant user profile management.
 *
 * Provides functionality for tenant users to view, update,
 * and delete their account profile with proper validation.
 */
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ProfileService $profileService Service for profile operations
     */
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Show the user's profile edit page.
     *
     * @param Request $request The incoming request
     * @return Response
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('app/Profile/Edit', [
            'user' => $request->user(),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     *
     * @param ProfileUpdateRequest $request The validated profile update request
     * @return RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->profileService->update(
            $request->user(),
            $request->validated(),
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'unknown'
        );

        return to_route('app.profile.edit')->with('success', 'Profil bilgileriniz güncellendi.');
    }
}
