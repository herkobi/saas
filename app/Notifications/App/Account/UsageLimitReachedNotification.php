<?php

/**
 * Usage Limit Reached Notification
 *
 * This notification is sent when a tenant reaches a feature usage limit.
 *
 * @package    App\Notifications\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Notifications\App\Account;

use App\Models\Feature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for usage limit reached.
 *
 * Sends only database notification when a feature limit is reached.
 */
class UsageLimitReachedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $tenantId The tenant ID
     * @param Feature $feature The feature that reached its limit
     * @param float $currentUsage The current usage amount
     * @param float $limit The limit that was reached
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly Feature $feature,
        private readonly float $currentUsage,
        private readonly float $limit
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'feature_id' => $this->feature->id,
            'title' => 'Kullanım Limiti Doldu',
            'message' => "{$this->feature->name} kullanım limitiniz doldu.",
            'type' => 'usage_limit_reached',
            'feature_name' => $this->feature->name,
            'feature_code' => $this->feature->slug,
            'current_usage' => $this->currentUsage,
            'limit' => $this->limit,
        ];
    }
}
