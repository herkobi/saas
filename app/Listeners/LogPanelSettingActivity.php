<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Shared\ActivityService;
use App\Events\PanelSettingUpdated;

class LogPanelSettingActivity
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function handleUpdated(PanelSettingUpdated $event): void
    {
        // Değişen key'leri hesapla
        $updatedKeys = array_keys(array_diff_assoc($event->newData, $event->oldData));

        $this->activityService->log(
            user: $event->user,
            type: 'panel.setting_updated',
            description: 'Panel ayarları güncellendi',
            log: [
                'updated_keys' => $updatedKeys,
                'old_data' => $event->oldData,
                'new_data' => $event->newData,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function subscribe($events): array
    {
        return [
            PanelSettingUpdated::class => 'handleUpdated',
        ];
    }
}
