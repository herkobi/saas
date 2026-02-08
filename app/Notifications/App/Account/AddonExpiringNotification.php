<?php

/**
 * Addon Expiring Notification
 *
 * Notification sent when an addon is about to expire.
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

use App\Mail\App\Account\AddonExpiringMail;
use App\Models\Addon;
use App\Models\TenantAddon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for addon expiring soon.
 *
 * Sends database and email notifications to the tenant owner.
 */
class AddonExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Addon $addon The expiring addon
     * @param TenantAddon $tenantAddon The tenant addon pivot
     * @param int $daysRemaining Days remaining until expiration
     */
    public function __construct(
        private readonly Addon $addon,
        private readonly TenantAddon $tenantAddon,
        private readonly int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        $this->addon->load('feature');

        return [
            'tenant_id' => $this->tenantAddon->tenant_id,
            'type' => 'addon.expiring',
            'title' => 'Eklenti Süresi Dolmak Üzere',
            'message' => sprintf(
                '%s eklentisinin süresi %d gün içinde dolacak.',
                $this->addon->name,
                $this->daysRemaining
            ),
            'addon_id' => $this->addon->id,
            'addon_name' => $this->addon->name,
            'addon_type' => $this->addon->addon_type->value,
            'feature_name' => $this->addon->feature->name,
            'quantity' => $this->tenantAddon->quantity,
            'expires_at' => $this->tenantAddon->expires_at?->toISOString(),
            'days_remaining' => $this->daysRemaining,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return AddonExpiringMail
     */
    public function toMail(mixed $notifiable): AddonExpiringMail
    {
        return new AddonExpiringMail($notifiable, $this->addon, $this->tenantAddon, $this->daysRemaining);
    }
}
