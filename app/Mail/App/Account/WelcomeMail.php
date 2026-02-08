<?php

/**
 * Welcome Mail
 *
 * This mailable handles the email notification sent when a new tenant
 * account is successfully registered. It provides tenant information
 * and welcome message.
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
 * Mailable for tenant welcome notifications.
 *
 * Sends an email to the user welcoming them with their
 * tenant information.
 */
class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user The newly registered user
     * @param Tenant $tenant The newly created tenant
     */
    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hoş Geldiniz!',
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
            markdown: 'mail.tenant.account.welcome',
            with: [
                'user' => $this->user,
                'tenant' => $this->tenant,
                'tenantName' => $this->tenant->data['name'] ?? $this->tenant->code,
                'dashboardUrl' => url('/app/dashboard'),
            ],
        );
    }
}
