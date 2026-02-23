<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Shared\ActivityService;
use App\Events\TenantBillingUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTenantBillingActivity implements ShouldQueue
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function handle(TenantBillingUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'tenant.billing_updated',
            description: 'Fatura bilgileri güncellendi',
            log: [
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'user_name' => $event->user->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
            ],
            tenantId: $event->tenant->id
        );
    }
}
