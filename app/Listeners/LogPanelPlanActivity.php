<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\PanelPlanCreated;
use App\Events\PanelPlanUpdated;
use App\Events\PanelPlanArchived;

class LogPanelPlanActivity
{
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    public function handleCreated(PanelPlanCreated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_created',
            description: 'Plan oluşturuldu',
            log: [
                'plan_id' => $event->plan->id,
                'plan_slug' => $event->plan->slug,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'created_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleUpdated(PanelPlanUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_updated',
            description: 'Plan güncellendi',
            log: [
                'plan_id' => $event->plan->id,
                'old_data' => $event->oldData,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleArchived(PanelPlanArchived $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_archived',
            description: 'Plan arşivlendi',
            log: [
                'plan_id' => $event->plan->id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'archived_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function subscribe($events): array
    {
        return [
            PanelPlanCreated::class => 'handleCreated',
            PanelPlanUpdated::class => 'handleUpdated',
            PanelPlanArchived::class => 'handleArchived',
        ];
    }
}
