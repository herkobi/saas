<?php

/**
 * Tenant Profile Service Interface Contract
 *
 * This interface defines the contract for tenant profile service
 * implementations, providing methods for profile management
 * within the tenant domain.
 *
 * @package    App\Contracts\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Profile;

use App\Models\User;

/**
 * Interface for tenant profile service implementations.
 *
 * Defines the contract for managing tenant user profiles
 * including update and delete operations with audit context.
 */
interface ProfileServiceInterface
{
    /**
     * Update the user's profile.
     *
     * @param User $user The user whose profile will be updated
     * @param array<string, mixed> $data The profile data to update
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return User The updated user instance
     */
    public function update(User $user, array $data, string $ipAddress, string $userAgent): User;
}
