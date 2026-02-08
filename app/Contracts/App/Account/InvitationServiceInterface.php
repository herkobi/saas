<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Collection;

interface InvitationServiceInterface
{
    /**
     * Invite a user by email. If user exists in system, add directly.
     * If not, create invitation and send email.
     *
     * @return array{type: string, invitation?: TenantInvitation, user?: User}
     */
    public function invite(
        Tenant $tenant,
        string $email,
        int $role,
        User $invitedBy,
        string $ipAddress,
        string $userAgent
    ): array;

    public function acceptInvitation(string $rawToken, User $user): TenantInvitation;

    public function acceptInvitationDirectly(TenantInvitation $invitation, User $user): void;

    public function revokeInvitation(
        Tenant $tenant,
        TenantInvitation $invitation,
        User $revokedBy,
        string $ipAddress,
        string $userAgent
    ): void;

    public function resendInvitation(
        Tenant $tenant,
        TenantInvitation $invitation,
        User $resentBy
    ): TenantInvitation;

    public function getPendingInvitations(Tenant $tenant): Collection;

    public function findById(Tenant $tenant, string $invitationId): ?TenantInvitation;

    public function findByToken(string $rawToken): ?TenantInvitation;

    public function expireOldInvitations(): int;

    public function getPendingInvitationCount(Tenant $tenant): int;
}
