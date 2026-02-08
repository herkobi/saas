<?php

/**
 * Addon Expiring Mail
 *
 * This mailable is sent when an addon is about to expire.
 *
 * @package    App\Mail\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Mail\App\Account;

use App\Models\Addon;
use App\Models\TenantAddon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for addon expiring soon notifications.
 *
 * Sends an email when an addon is about to expire.
 */
class AddonExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The user receiving the notification
     * @param Addon $addon The expiring addon
     * @param TenantAddon $tenantAddon The tenant addon pivot
     * @param int $daysRemaining Days remaining until expiration
     */
    public function __construct(
        public readonly User $user,
        public readonly Addon $addon,
        public readonly TenantAddon $tenantAddon,
        public readonly int $daysRemaining
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Eklenti Süresi Dolmak Üzere',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        $this->addon->load('feature');

        return new Content(
            markdown: 'mail.tenant.account.addon-expiring',
            with: [
                'user' => $this->user,
                'addon' => $this->addon,
                'tenantAddon' => $this->tenantAddon,
                'addonName' => $this->addon->name,
                'addonType' => $this->addon->addon_type->label(),
                'featureName' => $this->addon->feature->name,
                'quantity' => $this->tenantAddon->quantity,
                'expiresAt' => $this->tenantAddon->expires_at?->format('d.m.Y H:i'),
                'daysRemaining' => $this->daysRemaining,
                'renewUrl' => url('/app/account/addons'),
            ],
        );
    }
}
