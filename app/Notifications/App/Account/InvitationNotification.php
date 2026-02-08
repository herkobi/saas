<?php

declare(strict_types=1);

namespace App\Notifications\App\Account;

use App\Mail\App\Account\InvitationMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $inviterName;

    private string $tenantName;

    public function __construct(
        private readonly User $invitedBy,
        private readonly Tenant $tenant,
        private readonly string $acceptUrl,
        private readonly int $role
    ) {
        $this->inviterName = $invitedBy->name;
        $this->tenantName = $tenant->data['name'] ?? $tenant->code;
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): InvitationMail
    {
        return (new InvitationMail(
            $this->inviterName,
            $this->tenantName,
            $this->acceptUrl,
            $this->role
        ))->to($notifiable->routes['mail']);
    }
}
