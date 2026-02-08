<?php

declare(strict_types=1);

namespace App\Notifications\App\Account;

use App\Mail\App\Account\InvitationAcceptedMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvitationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $acceptedUser,
        private readonly Tenant $tenant
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): InvitationAcceptedMail
    {
        return (new InvitationAcceptedMail($this->acceptedUser, $this->tenant))
            ->to($notifiable->email);
    }

    public function toArray(mixed $notifiable): array
    {
        $tenantName = $this->tenant->data['name'] ?? $this->tenant->code;

        return [
            'type' => 'tenant.invitation_accepted',
            'title' => 'Davetiye Kabul Edildi',
            'message' => "{$this->acceptedUser->name}, '{$tenantName}' hesabına katıldı.",
            'tenant_id' => $this->tenant->id,
            'tenant_code' => $this->tenant->code,
            'tenant_name' => $tenantName,
            'accepted_user_id' => $this->acceptedUser->id,
            'accepted_user_name' => $this->acceptedUser->name,
        ];
    }
}
