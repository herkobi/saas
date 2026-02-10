<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\UserStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantUserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $user,
        public readonly UserStatus $oldStatus,
        public readonly UserStatus $newStatus,
        public readonly ?string $reason,
        public readonly User $changedBy,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
