<?php

declare(strict_types=1);

namespace App\Notifications\App\Account;

use App\Mail\App\Account\UserDirectlyAddedMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserDirectlyAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly User $addedBy
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): UserDirectlyAddedMail
    {
        return (new UserDirectlyAddedMail($notifiable, $this->tenant, $this->addedBy))
            ->to($notifiable->email);
    }

    public function toArray(mixed $notifiable): array
    {
        $tenantName = $this->tenant->data['name'] ?? $this->tenant->code;

        return [
            'type' => 'tenant.user_directly_added',
            'title' => 'Hesaba Eklendiniz',
            'message' => "'{$tenantName}' hesabına eklendiniz.",
            'tenant_id' => $this->tenant->id,
            'tenant_code' => $this->tenant->code,
            'tenant_name' => $tenantName,
            'added_by_name' => $this->addedBy->name,
        ];
    }
}
