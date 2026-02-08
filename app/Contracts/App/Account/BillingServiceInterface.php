<?php

/**
 * Billing Service Interface Contract
 *
 * This interface defines the contract for tenant billing service
 * implementations, providing methods for billing information
 * management within the tenant context.
 *
 * @package    App\Contracts\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Tenant;
use App\Models\User;

/**
 * Interface for tenant billing service implementations.
 *
 * Defines methods for retrieving and updating tenant billing information
 * including company details, tax information, and contact data.
 */
interface BillingServiceInterface
{
    /**
     * Get tenant account information.
     *
     * @param Tenant $tenant The tenant to retrieve account info for
     * @return array The account information array
     */
    public function getAccount(Tenant $tenant): array;

    /**
     * Update tenant account information.
     *
     * @param Tenant $tenant The tenant to update
     * @param array $data The data to update
     * @param User $user The user performing the update
     * @param string $ipAddress The IP address of the request
     * @param string $userAgent The user agent of the request
     * @return Tenant The updated tenant
     */
    public function updateAccount(Tenant $tenant, array $data, User $user, string $ipAddress, string $userAgent): Tenant;

    /**
     * Get tenant billing information.
     *
     * @param Tenant $tenant The tenant to retrieve billing info for
     * @return array The billing information array
     */
    public function getBillingInfo(Tenant $tenant): array;

    /**
     * Update tenant billing information.
     *
     * @param Tenant $tenant The tenant to update
     * @param array $data The billing data to update
     * @param User $user The user performing the update
     * @param string $ipAddress The IP address of the request
     * @param string $userAgent The user agent of the request
     * @return Tenant The updated tenant
     */
    public function updateBillingInfo(Tenant $tenant, array $data, User $user, string $ipAddress, string $userAgent): Tenant;
}
