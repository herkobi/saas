<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Shared\ActivityService;
use App\Events\PanelFeatureCreated;
use App\Events\PanelFeatureUpdated;
use App\Events\PanelFeatureDeleted;

class LogPanelFeatureActivity
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function handleCreated(PanelFeatureCreated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.feature_created',
            description: 'Özellik oluşturuldu',
            log: [
                'feature_id' => $event->feature->id,
                'feature_slug' => $event->feature->slug,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'created_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleUpdated(PanelFeatureUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.feature_updated',
            description: 'Özellik güncellendi',
            log: [
                'feature_id' => $event->feature->id,
                'old_data' => $event->oldData,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleDeleted(PanelFeatureDeleted $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.feature_deleted',
            description: 'Özellik silindi',
            log: [
                'feature_id' => $event->feature->id,
                'feature_slug' => $event->feature->slug,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'deleted_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function subscribe($events): array
    {
        return [
            PanelFeatureCreated::class => 'handleCreated',
            PanelFeatureUpdated::class => 'handleUpdated',
            PanelFeatureDeleted::class => 'handleDeleted',
        ];
    }
}
