<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\InvitationStatus;
use App\Models\TenantInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireOldInvitationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;

    public int $timeout = 30;

    public function handle(): void
    {
        TenantInvitation::withoutTenantScope()
            ->where('status', InvitationStatus::PENDING)
            ->where('expires_at', '<=', now())
            ->update(['status' => InvitationStatus::EXPIRED]);
    }
}
