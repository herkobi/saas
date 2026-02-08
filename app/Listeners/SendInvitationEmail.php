<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TenantUserInvited;
use App\Notifications\App\Account\InvitationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Notification;

class SendInvitationEmail implements ShouldQueue
{
    public function handleInvitationSent(TenantUserInvited $event): void
    {
        $acceptUrl = url('/app/invitation/accept/'.$event->rawToken);

        Notification::route('mail', $event->invitation->email)
            ->notify(new InvitationNotification(
                $event->invitedBy,
                $event->tenant,
                $acceptUrl,
                $event->invitation->role->value
            ));
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantUserInvited::class => 'handleInvitationSent',
        ];
    }
}
