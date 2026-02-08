<?php

/**
 * User Removed From Tenant Mail
 *
 * Mailable class for user removal notifications.
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

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for user removed from tenant notification.
 */
class UserRemovedFromTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The tenant name.
     *
     * @var string
     */
    public string $tenantName;

    /**
     * Create a new message instance.
     *
     * @param User $user The user who was removed
     * @param Tenant $tenant The tenant the user was removed from
     */
    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant
    ) {
        $this->tenantName = $tenant->data['name'] ?? $tenant->code;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hesaptan Çıkarıldınız',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant.account.user-removed',
            with: [
                'user' => $this->user,
                'tenantName' => $this->tenantName,
            ],
        );
    }
}
