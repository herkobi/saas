<?php

/**
 * Panel Profile Service
 *
 * This service handles all panel profile-related operations including
 * profile updates with comprehensive audit logging.
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

use App\Contracts\Panel\Profile\ProfileServiceInterface;
use App\Events\PanelProfileUpdated;
use App\Models\User;

/**
 * Panel Profile Service
 *
 * Service implementation for managing panel user profiles
 * including update operations with event dispatching.
 */
class ProfileService implements ProfileServiceInterface
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
    public function update(User $user, array $data, string $ipAddress, string $userAgent): User
    {
        $originalData = $user->only(array_keys($data));

        $user->fill($data);
        $user->save();

        $changes = [];
        foreach ($data as $key => $value) {
            if (isset($originalData[$key]) && $originalData[$key] !== $value) {
                $changes[$key] = [
                    'old' => $originalData[$key],
                    'new' => $value,
                ];
            }
        }

        PanelProfileUpdated::dispatch($user, $changes, $ipAddress, $userAgent);

        return $user;
    }

}
