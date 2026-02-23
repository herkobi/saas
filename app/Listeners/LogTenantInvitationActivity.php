<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Shared\ActivityService;
use App\Enums\TenantUserRole;
use App\Events\TenantInvitationAccepted;
use App\Events\TenantInvitationRevoked;
use App\Events\TenantUserDirectlyAdded;
use App\Events\TenantUserInvited;
use App\Notifications\App\Account\InvitationAcceptedNotification;
use App\Notifications\App\Account\UserDirectlyAddedNotification;
use Illuminate\Events\Dispatcher;

class LogTenantInvitationActivity
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function handleUserInvited(TenantUserInvited $event): void
    {
        $this->activityService->log(
            user: $event->invitedBy,
            type: 'tenant.user_invited',
            description: 'Kullanıcı davet edildi',
            log: [
                'tenant_id' => $event->tenant->id,
                'invited_email' => $event->invitation->email,
                'role' => $event->invitation->role->value,
                'role_label' => $event->invitation->role->label(),
                'invited_by_name' => $event->invitedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );
    }

    public function handleUserDirectlyAdded(TenantUserDirectlyAdded $event): void
    {
        $roleLabel = TenantUserRole::from($event->role)->label();

        $this->activityService->log(
            user: $event->addedBy,
            type: 'tenant.user_directly_added',
            description: 'Kullanıcı doğrudan eklendi',
            log: [
                'tenant_id' => $event->tenant->id,
                'added_user_id' => $event->user->id,
                'added_user_email' => $event->user->email,
                'role' => $event->role,
                'role_label' => $roleLabel,
                'added_by_name' => $event->addedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );

        $event->user->notify(new UserDirectlyAddedNotification($event->tenant, $event->addedBy));
    }

    public function handleInvitationAccepted(TenantInvitationAccepted $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.invitation_accepted',
            description: 'Davetiye kabul edildi',
            log: [
                'tenant_id' => $event->tenant->id,
                'invitation_id' => $event->invitation->id,
                'accepted_user_id' => $event->user->id,
                'accepted_user_email' => $event->user->email,
                'role' => $event->invitation->role->value,
                'role_label' => $event->invitation->role->label(),
            ],
            tenantId: $event->tenant->id
        );

        $inviter = $event->invitation->invitedByUser;

        if ($inviter) {
            $inviter->notify(new InvitationAcceptedNotification($event->user, $event->tenant));
        }
    }

    public function handleInvitationRevoked(TenantInvitationRevoked $event): void
    {
        $this->activityService->log(
            user: $event->revokedBy,
            type: 'tenant.invitation_revoked',
            description: 'Davetiye iptal edildi',
            log: [
                'tenant_id' => $event->tenant->id,
                'invitation_id' => $event->invitation->id,
                'invitation_email' => $event->invitation->email,
                'revoked_by_name' => $event->revokedBy->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantUserInvited::class => 'handleUserInvited',
            TenantUserDirectlyAdded::class => 'handleUserDirectlyAdded',
            TenantInvitationAccepted::class => 'handleInvitationAccepted',
            TenantInvitationRevoked::class => 'handleInvitationRevoked',
        ];
    }
}
