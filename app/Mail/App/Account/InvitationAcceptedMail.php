<?php

declare(strict_types=1);

namespace App\Mail\App\Account;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $tenantName;

    public function __construct(
        public readonly User $acceptedUser,
        public readonly Tenant $tenant
    ) {
        $this->tenantName = $tenant->data['name'] ?? $tenant->code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Davetiye Kabul Edildi',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant.account.invitation-accepted',
            with: [
                'acceptedUser' => $this->acceptedUser,
                'tenantName' => $this->tenantName,
            ],
        );
    }
}
