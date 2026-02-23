<?php

/**
 * Tenant Profile Service
 *
 * This service handles all tenant profile-related operations including
 * profile updates and deletions with comprehensive audit logging.
 *
 * @package    App\Services\App\Profile
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\App\Profile;

use App\Events\TenantProfileDeleted;
use App\Events\TenantProfileUpdated;
use App\Models\User;
use App\Traits\HasTenantContext;

/**
 * Tenant Profile Service
 *
 * Service implementation for managing tenant user profiles
 * including update and delete operations with event dispatching.
 */
class ProfileService
{
    use HasTenantContext;

    /**
     * Update the user's profile.
     *
     * @param User $user The user whose profile will be updated
     * @param array<string, mixed> $data The profile data to update
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return User The updated user instance
     */
    public function update(User $user, array $data, string $ipAddress, string $userAgent): User
    {
        $originalData = $user->only(array_keys($data));

        $user->fill($data);
        $user->save();

        $changes = [];
        foreach ($data as $key => $value) {
            if ($originalData[$key] !== $value) {
                $changes[$key] = [
                    'old' => $originalData[$key],
                    'new' => $value,
                ];
            }
        }

        TenantProfileUpdated::dispatch(
            $user,
            $this->requireTenant(),
            $changes,
            $ipAddress,
            $userAgent
        );

        return $user;
    }
}
