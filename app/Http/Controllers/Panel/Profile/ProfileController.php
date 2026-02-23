<?php

/**
 * Panel Profile Controller
 *
 * This controller handles profile management operations for panel users
 * including displaying the profile edit form and processing profile updates.
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

use App\Services\Panel\Profile\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Profile\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel user profile management.
 *
 * Provides functionality for panel users to view and update
 * their account profile with proper validation.
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
        return Inertia::render('panel/Profile/Edit', [
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

        return back()->with('success', 'Profil bilgileriniz güncellendi.');
    }

}
