<?php

/**
 * Panel Password Service Interface Contract
 *
 * This interface defines the contract for panel password service
 * implementations, providing methods for password management
 * within the panel domain.
 *
 * @package    App\Contracts\Panel\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Panel\Profile;

use App\Models\User;

/**
 * Interface for panel password service implementations.
 *
 * Defines the contract for managing panel user passwords
 * including update operations with audit context.
 */
interface PasswordServiceInterface
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
    public function update(User $user, string $password, string $ipAddress, string $userAgent): User;
}
