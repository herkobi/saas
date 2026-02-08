<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\PanelPlanPriceCreated;
use App\Events\PanelPlanPriceUpdated;
use App\Events\PanelPlanPriceDeleted;

class LogPanelPlanPriceActivity
{
    public function __construct(
        private readonly ActivityServiceInterface $activityService
    ) {}

    public function handleCreated(PanelPlanPriceCreated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_price_created',
            description: 'Plan fiyatı oluşturuldu',
            log: [
                'plan_price_id' => $event->price->id,
                'plan_id' => $event->price->plan_id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'created_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleUpdated(PanelPlanPriceUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_price_updated',
            description: 'Plan fiyatı güncellendi',
            log: [
                'plan_price_id' => $event->price->id,
                'plan_id' => $event->price->plan_id,
                'old_data' => $event->oldData,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function handleDeleted(PanelPlanPriceDeleted $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.plan_price_deleted',
            description: 'Plan fiyatı silindi',
            log: [
                'plan_price_id' => $event->price->id,
                'plan_id' => $event->price->plan_id,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'deleted_at' => now()->toDateTimeString(),
            ]
        );
    }

    public function subscribe($events): array
    {
        return [
            PanelPlanPriceCreated::class => 'handleCreated',
            PanelPlanPriceUpdated::class => 'handleUpdated',
            PanelPlanPriceDeleted::class => 'handleDeleted',
        ];
    }
}
