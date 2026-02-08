<?php

/**
 * Panel Two Factor Service Interface Contract
 *
 * This interface defines the contract for panel two-factor authentication
 * service implementations, providing methods for two-factor management
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
 * Interface for panel two-factor authentication service implementations.
 *
 * Defines the contract for managing panel user two-factor authentication
 * including enable and disable operations with audit context.
 */
interface TwoFactorServiceInterface
{
    /**
     * Enable two-factor authentication for the user.
     *
     * @param User $user The user enabling two-factor authentication
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function enable(User $user, string $ipAddress, string $userAgent): void;

    /**
     * Disable two-factor authentication for the user.
     *
     * @param User $user The user disabling two-factor authentication
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return void
     */
    public function disable(User $user, string $ipAddress, string $userAgent): void;
}
