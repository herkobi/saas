<?php

/**
 * Tenant User Role Enum
 *
 * Defines the roles assigned to users within a tenant organization.
 * Used for authorization and access control within the tenant context.
 *
 * @package    App\Enums
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Enums;

enum TenantUserRole: int
{
    /**
     * The owner of the tenant account.
     * Has full access to all tenant settings and billing.
     */
    case OWNER = 1;

    /**
     * Regular staff member.
     * Access is limited based on permissions (if implemented) or general staff access.
     */
    case STAFF = 2;

    /**
     * Get the human-readable label for the role.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Şirket Sahibi',
            self::STAFF => 'Personel',
        };
    }

    /**
     * Get the color code/variant for UI badges.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::OWNER => 'primary',
            self::STAFF => 'info',
        };
    }
}
