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

class UserDirectlyAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $tenantName;

    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant,
        public readonly User $addedBy
    ) {
        $this->tenantName = $tenant->data['name'] ?? $tenant->code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hesaba Eklendiniz',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.tenant.account.user-directly-added',
            with: [
                'user' => $this->user,
                'tenantName' => $this->tenantName,
                'addedBy' => $this->addedBy,
            ],
        );
    }
}
