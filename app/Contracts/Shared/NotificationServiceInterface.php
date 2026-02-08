<?php

/**
 * Notification Service Interface Contract
 *
 * This interface defines the contract for notification service
 * implementations, providing methods for notification retrieval,
 * marking as read, and archiving.
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
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for notification service implementations.
 *
 * This interface defines the contract for managing user notifications
 * including retrieval, reading, and archiving operations.
 */
interface NotificationServiceInterface
{
    /**
     * Get paginated notifications for the authenticated user.
     *
     * @param User $user The user whose notifications to retrieve
     * @param int $perPage Number of notifications per page
     * @return LengthAwarePaginator Paginated notification results
     */
    public function getPaginatedNotifications(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get paginated archived notifications for the authenticated user.
     *
     * @param User $user The user whose archived notifications to retrieve
     * @param int $perPage Number of notifications per page
     * @return LengthAwarePaginator Paginated archived notification results
     */
    public function getPaginatedArchivedNotifications(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Mark a notification as read.
     *
     * @param User $user The user who owns the notification
     * @param string $notificationId The notification ID
     * @return bool True if successful
     */
    public function markAsRead(User $user, string $notificationId): bool;

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @param User $user The user whose notifications to mark as read
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(User $user): int;

    /**
     * Get unread notification count for the authenticated user.
     *
     * @param User $user The user whose unread count to retrieve
     * @return int Number of unread notifications
     */
    public function getUnreadCount(User $user): int;

    /**
     * Delete all active notifications for a user.
     *
     * @param User $user The user whose notifications to delete
     * @return int Number of notifications deleted
     */
    public function deleteAllForUser(User $user): int;

    /**
     * Anonymize archived notifications for a user.
     *
     * Masks PII fields (user_name, user_email) in notification data
     * and nullifies the notifiable_id reference.
     *
     * @param User $user The user whose archived notifications to anonymize
     * @return int Number of archived notifications anonymized
     */
    public function anonymizeArchivedForUser(User $user): int;
}
