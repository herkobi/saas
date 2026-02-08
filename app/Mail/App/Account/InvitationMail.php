<?php

declare(strict_types=1);

namespace App\Mail\App\Account;

use App\Enums\TenantUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $roleLabel;

    public function __construct(
        public readonly string $inviterName,
        public readonly string $tenantName,
        public readonly string $acceptUrl,
        public readonly int $role
    ) {
        $this->roleLabel = TenantUserRole::from($role)->label();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ekip Davetiyesi',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant.account.invitation',
            with: [
                'inviterName' => $this->inviterName,
                'tenantName' => $this->tenantName,
                'acceptUrl' => $this->acceptUrl,
                'roleLabel' => $this->roleLabel,
            ],
        );
    }
}
