<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantUserInvited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly TenantInvitation $invitation,
        public readonly string $rawToken,
        public readonly User $invitedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
