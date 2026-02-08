<?php

/**
 * User Anonymization Service Interface Contract
 *
 * This interface defines the contract for user anonymization operations
 * including PII masking, notification cleanup, and activity anonymization.
 *
 * @package    App\Contracts\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Shared;

use App\Models\User;

/**
 * Interface for user anonymization service implementations.
 *
 * Defines the contract for anonymizing user data in compliance
 * with KVKK/GDPR requirements.
 */
interface UserAnonymizationServiceInterface
{
    /**
     * Anonymize a user and all associated personal data.
     *
     * This operation:
     * - Anonymizes user's activity logs (masks PII)
     * - Deletes active notifications
     * - Anonymizes archived notifications (masks PII, nullifies reference)
     * - Masks user profile data (name, email)
     * - Clears authentication tokens
     * - Soft deletes the user
     *
     * @param User $user The user to anonymize
     * @return void
     */
    public function anonymize(User $user): void;
}
