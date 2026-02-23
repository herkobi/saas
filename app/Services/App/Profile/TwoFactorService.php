<?php

/**
 * Tenant Two Factor Service
 *
 * This service handles all tenant two-factor authentication operations
 * including enable and disable with comprehensive audit logging.
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

use App\Events\TenantTwoFactorDisabled;
use App\Events\TenantTwoFactorEnabled;
use App\Models\User;
use App\Traits\HasTenantContext;

/**
 * Tenant Two Factor Service
 *
 * Service implementation for managing tenant user two-factor authentication
 * including enable and disable operations with event dispatching.
 */
class TwoFactorService
{
    use HasTenantContext;

    /**
     * Enable two-factor authentication for the user.
     *
     * @param User $user The user enabling two-factor authentication
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function enable(User $user, string $ipAddress, string $userAgent): void
    {
        TenantTwoFactorEnabled::dispatch(
            $user,
            $this->requireTenant(),
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param User $user The user disabling two-factor authentication
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function disable(User $user, string $ipAddress, string $userAgent): void
    {
        TenantTwoFactorDisabled::dispatch(
            $user,
            $this->requireTenant(),
            $ipAddress,
            $userAgent
        );
    }
}
