<?php

/**
 * Tenant Password Service
 *
 * This service handles all tenant password-related operations including
 * password updates with comprehensive audit logging.
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

use App\Contracts\App\Profile\PasswordServiceInterface;
use App\Events\TenantPasswordChanged;
use App\Models\User;
use App\Traits\HasTenantContext;

/**
 * Tenant Password Service
 *
 * Service implementation for managing tenant user passwords
 * including update operations with event dispatching.
 */
class PasswordService implements PasswordServiceInterface
{
    use HasTenantContext;

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

        TenantPasswordChanged::dispatch(
            $user,
            $this->requireTenant(),
            $ipAddress,
            $userAgent
        );

        return $user;
    }
}
