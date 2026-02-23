<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Services\App\Account\FeatureUsageService;
use App\Services\Shared\TenantContextService;
use App\Enums\InvitationStatus;
use App\Events\TenantInvitationAccepted;
use App\Events\TenantInvitationRevoked;
use App\Events\TenantUserDirectlyAdded;
use App\Events\TenantUserInvited;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvitationService
{
    public function __construct(
        private readonly TenantContextService $tenantContextService,
        private readonly FeatureUsageService $featureUsageService
    ) {}

    public function invite(
        Tenant $tenant,
        string $email,
        int $role,
        User $invitedBy,
        string $ipAddress,
        string $userAgent
    ): array {
        if (! $this->tenantContextService->canInviteTeamMember($tenant)) {
            throw new \InvalidArgumentException(
                'Kullanıcı davet etme izniniz yok veya kullanıcı limitine ulaştınız.'
            );
        }

        $this->checkLimitWithPendingInvitations($tenant);

        if ($tenant->users()->where('users.email', $email)->exists()) {
            throw new \InvalidArgumentException('Bu e-posta adresi zaten bu hesaba kayıtlı.');
        }

        $existingInvitation = TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->where('status', InvitationStatus::PENDING)
            ->first();

        if ($existingInvitation) {
            throw new \InvalidArgumentException('Bu e-posta adresi için zaten bekleyen bir davetiye var.');
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return $this->addUserDirectly($tenant, $existingUser, $role, $invitedBy, $ipAddress, $userAgent);
        }

        return $this->createInvitation($tenant, $email, $role, $invitedBy, $ipAddress, $userAgent);
    }

    public function acceptInvitation(string $rawToken, User $user): TenantInvitation
    {
        $hashedToken = hash('sha256', $rawToken);

        /** @var TenantInvitation|null $invitation */
        $invitation = TenantInvitation::query()
            ->where('token', $hashedToken)
            ->where('status', InvitationStatus::PENDING)
            ->first();

        if (! $invitation) {
            throw new \InvalidArgumentException('Davetiye bulunamadı veya geçersiz.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => InvitationStatus::EXPIRED]);
            throw new \InvalidArgumentException('Davetiye süresi dolmuş.');
        }

        if ($invitation->email !== $user->email) {
            throw new \InvalidArgumentException('Bu davetiye sizin e-posta adresinize ait değil.');
        }

        $tenant = Tenant::find($invitation->tenant_id);

        if (! $tenant) {
            $invitation->update(['status' => InvitationStatus::EXPIRED]);
            throw new \InvalidArgumentException('Davetiye geçersiz. İlgili hesap artık mevcut değil.');
        }

        if ($tenant->users()->where('users.id', $user->id)->exists()) {
            throw new \InvalidArgumentException('Bu hesaba zaten üyesiniz.');
        }

        return DB::transaction(function () use ($invitation, $user, $tenant) {
            $tenant->users()->attach($user->id, [
                'role' => $invitation->role->value,
                'joined_at' => now(),
            ]);

            $this->featureUsageService->incrementUsage($tenant, 'users');

            $invitation->update([
                'status' => InvitationStatus::ACCEPTED,
                'accepted_by' => $user->id,
                'accepted_at' => now(),
            ]);

            event(new TenantInvitationAccepted($tenant, $invitation->fresh(), $user));

            return $invitation->fresh();
        });
    }

    public function acceptInvitationDirectly(TenantInvitation $invitation, User $user): void
    {
        $tenant = Tenant::find($invitation->tenant_id);

        if (! $tenant || ! $invitation->isPending() || $invitation->isExpired()) {
            return;
        }

        if ($tenant->users()->where('users.id', $user->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($invitation, $user, $tenant) {
            $tenant->users()->attach($user->id, [
                'role' => $invitation->role->value,
                'joined_at' => now(),
            ]);

            $this->featureUsageService->incrementUsage($tenant, 'users');

            $invitation->update([
                'status' => InvitationStatus::ACCEPTED,
                'accepted_by' => $user->id,
                'accepted_at' => now(),
            ]);

            event(new TenantInvitationAccepted($tenant, $invitation->fresh(), $user));
        });
    }

    public function revokeInvitation(
        Tenant $tenant,
        TenantInvitation $invitation,
        User $revokedBy,
        string $ipAddress,
        string $userAgent
    ): void {
        if (! $invitation->isPending()) {
            throw new \InvalidArgumentException('Sadece bekleyen davetiyeler iptal edilebilir.');
        }

        $invitation->update(['status' => InvitationStatus::REVOKED]);

        event(new TenantInvitationRevoked($tenant, $invitation->fresh(), $revokedBy, $ipAddress, $userAgent));
    }

    public function resendInvitation(
        Tenant $tenant,
        TenantInvitation $invitation,
        User $resentBy
    ): TenantInvitation {
        if (! $invitation->isPending()) {
            throw new \InvalidArgumentException('Sadece bekleyen davetiyeler tekrar gönderilebilir.');
        }

        $rawToken = Str::random(64);
        $hashedToken = hash('sha256', $rawToken);

        $invitation->update([
            'token' => $hashedToken,
            'expires_at' => now()->addDays((int) config('herkobi.invitation.expires_days', 7)),
        ]);

        event(new TenantUserInvited(
            $tenant,
            $invitation->fresh(),
            $rawToken,
            $resentBy,
            request()->ip() ?? '127.0.0.1',
            request()->userAgent() ?? 'unknown'
        ));

        return $invitation->fresh();
    }

    public function getPendingInvitations(Tenant $tenant): Collection
    {
        return TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', InvitationStatus::PENDING)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(Tenant $tenant, string $invitationId): ?TenantInvitation
    {
        return TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->find($invitationId);
    }

    public function findByToken(string $rawToken): ?TenantInvitation
    {
        $hashedToken = hash('sha256', $rawToken);

        return TenantInvitation::query()
            ->where('token', $hashedToken)
            ->where('status', InvitationStatus::PENDING)
            ->first();
    }

    public function expireOldInvitations(): int
    {
        return TenantInvitation::query()
            ->where('status', InvitationStatus::PENDING)
            ->where('expires_at', '<=', now())
            ->update(['status' => InvitationStatus::EXPIRED]);
    }

    public function getPendingInvitationCount(Tenant $tenant): int
    {
        return TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', InvitationStatus::PENDING)
            ->where('expires_at', '>', now())
            ->count();
    }

    private function addUserDirectly(
        Tenant $tenant,
        User $user,
        int $role,
        User $addedBy,
        string $ipAddress,
        string $userAgent
    ): array {
        DB::transaction(function () use ($tenant, $user, $role) {
            $tenant->users()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);

            $this->featureUsageService->incrementUsage($tenant, 'users');
        });

        event(new TenantUserDirectlyAdded($tenant, $user, $role, $addedBy, $ipAddress, $userAgent));

        return ['type' => 'directly_added', 'user' => $user];
    }

    private function createInvitation(
        Tenant $tenant,
        string $email,
        int $role,
        User $invitedBy,
        string $ipAddress,
        string $userAgent
    ): array {
        $rawToken = Str::random(64);
        $hashedToken = hash('sha256', $rawToken);

        $invitation = TenantInvitation::create([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'role' => $role,
            'token' => $hashedToken,
            'status' => InvitationStatus::PENDING,
            'invited_by' => $invitedBy->id,
            'expires_at' => now()->addDays((int) config('herkobi.invitation.expires_days', 7)),
        ]);

        event(new TenantUserInvited($tenant, $invitation, $rawToken, $invitedBy, $ipAddress, $userAgent));

        return ['type' => 'invited', 'invitation' => $invitation];
    }

    private function checkLimitWithPendingInvitations(Tenant $tenant): void
    {
        $limitCheck = $this->featureUsageService->checkFeatureLimit($tenant, 'users');

        if (! $limitCheck['allowed']) {
            throw new \InvalidArgumentException(
                'Kullanıcı limitine ulaştınız. Daha fazla kullanıcı eklemek için planınızı yükseltin.'
            );
        }

        if (isset($limitCheck['remaining']) && is_numeric($limitCheck['remaining'])) {
            $pendingCount = $this->getPendingInvitationCount($tenant);

            if ($pendingCount >= (int) $limitCheck['remaining']) {
                throw new \InvalidArgumentException(
                    'Bekleyen davetiyeler dahil kullanıcı limitine ulaştınız.'
                );
            }
        }
    }
}
