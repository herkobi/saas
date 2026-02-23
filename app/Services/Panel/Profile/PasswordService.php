<?php

/**
 * Panel Password Service
 *
 * This service handles all panel password-related operations including
 * password updates with comprehensive audit logging.
 *
 * @package    App\Services\Panel\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Profile;

use App\Events\PanelPasswordChanged;
use App\Models\User;

/**
 * Panel Password Service
 *
 * Service implementation for managing panel user passwords
 * including update operations with event dispatching.
 */
class PasswordService
{
    /**
     * Update the user's password.
     *
     * @param User $user The user whose password will be updated
     * @param string $password The new password
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return User The updated user instance
     */
    public function update(User $user, string $password, string $ipAddress, string $userAgent): User
    {
        $user->update(['password' => $password]);

        PanelPasswordChanged::dispatch($user, $ipAddress, $userAgent);

        return $user;
    }
}
