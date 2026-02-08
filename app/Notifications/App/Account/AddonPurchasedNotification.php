<?php

/**
 * Addon Purchased Notification
 *
 * Notification sent when a tenant purchases an addon.
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

use App\Mail\App\Account\AddonPurchasedMail;
use App\Models\Addon;
use App\Models\TenantAddon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for addon purchase.
 *
 * Sends database and email notifications to the tenant owner.
 */
class AddonPurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Addon $addon The purchased addon
     * @param TenantAddon $tenantAddon The tenant addon pivot
     */
    public function __construct(
        private readonly Addon $addon,
        private readonly TenantAddon $tenantAddon
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
            'type' => 'addon.purchased',
            'title' => 'Eklenti Satın Alındı',
            'message' => sprintf(
                '%s eklentisi başarıyla satın alındı.',
                $this->addon->name
            ),
            'addon_id' => $this->addon->id,
            'addon_name' => $this->addon->name,
            'addon_type' => $this->addon->addon_type->value,
            'feature_name' => $this->addon->feature->name,
            'quantity' => $this->tenantAddon->quantity,
            'price' => $this->tenantAddon->getTotalPrice(),
            'currency' => $this->tenantAddon->getEffectiveCurrency(),
            'started_at' => $this->tenantAddon->started_at?->toISOString(),
            'expires_at' => $this->tenantAddon->expires_at?->toISOString(),
            'is_recurring' => $this->addon->is_recurring,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return AddonPurchasedMail
     */
    public function toMail(mixed $notifiable): AddonPurchasedMail
    {
        return new AddonPurchasedMail($notifiable, $this->addon, $this->tenantAddon);
    }
}
