<?php

/**
 * Notification Service
 *
 * This service handles all notification-related operations including retrieval,
 * marking as read, and managing archived notifications.
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

use App\Helpers\MaskHelper;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Notification Service
 *
 * Service implementation for managing user notifications including
 * retrieval, reading, and archiving operations.
 */
class NotificationService
{
    /**
     * Get paginated notifications for the authenticated user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get paginated archived notifications for the authenticated user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedArchivedNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return DB::table('archived_notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('archived_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mark a notification as read.
     *
     * @param User $user
     * @param string $notificationId
     * @return bool
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return false;
        }

        if ($notification instanceof \Illuminate\Notifications\DatabaseNotification || method_exists($notification, 'markAsRead')) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @param User $user
     * @return int
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count for the authenticated user.
     *
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Delete all active notifications for a user.
     *
     * @param User $user
     * @return int
     */
    public function deleteAllForUser(User $user): int
    {
        return $user->notifications()->delete();
    }

    /**
     * Anonymize archived notifications for a user.
     *
     * @param User $user
     * @return int
     */
    public function anonymizeArchivedForUser(User $user): int
    {
        $count = 0;

        DB::table('archived_notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->chunkById(100, function ($notifications) use (&$count) {
                foreach ($notifications as $notification) {
                    $data = json_decode($notification->data, true);

                    if (isset($data['user_name'])) {
                        $data['user_name'] = MaskHelper::name($data['user_name']);
                    }

                    if (isset($data['user_email'])) {
                        $data['user_email'] = MaskHelper::email($data['user_email']);
                    }

                    DB::table('archived_notifications')
                        ->where('id', $notification->id)
                        ->update([
                            'notifiable_id' => null,
                            'data' => json_encode($data),
                            'updated_at' => now(),
                        ]);

                    $count++;
                }
            });

        return $count;
    }
}
