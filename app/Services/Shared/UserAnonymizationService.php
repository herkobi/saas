<?php

/**
 * User Anonymization Service
 *
 * This service orchestrates the anonymization of user data across
 * all related entities (activities, notifications, profile).
 *
 * @package    App\Services\Shared
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Shared;

use App\Contracts\Shared\NotificationServiceInterface;
use App\Contracts\Shared\UserAnonymizationServiceInterface;
use App\Helpers\MaskHelper;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * User Anonymization Service
 *
 * Handles complete user anonymization including activity logs,
 * notifications, and profile data masking for KVKK/GDPR compliance.
 */
class UserAnonymizationService implements UserAnonymizationServiceInterface
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService,
    ) {}

    /**
     * Anonymize a user and all associated personal data.
     *
     * @param User $user
     * @return void
     */
    public function anonymize(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->anonymizeActivities($user);
            $this->notificationService->deleteAllForUser($user);
            $this->notificationService->anonymizeArchivedForUser($user);
            $this->maskUserProfile($user);
        });
    }

    /**
     * Anonymize all activity logs for a user.
     *
     * @param User $user
     * @return void
     */
    private function anonymizeActivities(User $user): void
    {
        Activity::where('user_id', $user->id)
            ->whereNotNull('user_id')
            ->each(function (Activity $activity) {
                $activity->anonymize();
            });
    }

    /**
     * Mask user profile data and soft delete.
     *
     * @param User $user
     * @return void
     */
    private function maskUserProfile(User $user): void
    {
        $user->update([
            'name' => MaskHelper::name($user->name),
            'email' => MaskHelper::email($user->email),
            'password' => '',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'remember_token' => null,
            'anonymized_at' => now(),
        ]);

        $user->delete();
    }
}
