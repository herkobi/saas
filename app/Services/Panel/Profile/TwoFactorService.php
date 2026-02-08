<?php

/**
 * Panel Two Factor Service
 *
 * This service handles all panel two-factor authentication operations
 * including enable and disable with comprehensive audit logging.
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

use App\Contracts\Panel\Profile\TwoFactorServiceInterface;
use App\Events\PanelTwoFactorDisabled;
use App\Events\PanelTwoFactorEnabled;
use App\Models\User;

/**
 * Panel Two Factor Service
 *
 * Service implementation for managing panel user two-factor authentication
 * including enable and disable operations with event dispatching.
 */
class TwoFactorService implements TwoFactorServiceInterface
{
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
        PanelTwoFactorEnabled::dispatch($user, $ipAddress, $userAgent);
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
        PanelTwoFactorDisabled::dispatch($user, $ipAddress, $userAgent);
    }
}
