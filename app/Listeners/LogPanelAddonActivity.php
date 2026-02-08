<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\PanelAddonCreated;
use App\Events\PanelAddonDeleted;
use App\Events\PanelAddonUpdated;
use Illuminate\Events\Dispatcher;

class LogPanelAddonActivity
{
    public function __construct(
        protected ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle addon created event.
     */
    public function handleCreated(PanelAddonCreated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.addon_created',
            description: 'Eklenti oluşturuldu',
            log: [
                'addon_id' => $event->addon->id,
                'addon_name' => $event->addon->name,
                'addon_slug' => $event->addon->slug,
                'addon_type' => $event->addon->addon_type->value,
                'feature_id' => $event->addon->feature_id,
                'feature_name' => $event->addon->feature->name,
                'price' => $event->addon->price,
                'currency' => $event->addon->currency,
                'is_recurring' => $event->addon->is_recurring,
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'created_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle addon updated event.
     */
    public function handleUpdated(PanelAddonUpdated $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.addon_updated',
            description: 'Eklenti güncellendi',
            log: [
                'addon_id' => $event->addon->id,
                'addon_name' => $event->addon->name,
                'addon_slug' => $event->addon->slug,
                'addon_type' => $event->addon->addon_type->value,
                'changes' => $event->addon->getChanges(),
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'updated_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle addon deleted event.
     */
    public function handleDeleted(PanelAddonDeleted $event): void
    {
        $this->activityService->log(
            user: $event->user,
            type: 'panel.addon_deleted',
            description: 'Eklenti silindi',
            log: [
                'addon_id' => $event->addon->id,
                'addon_name' => $event->addon->name,
                'addon_slug' => $event->addon->slug,
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'deleted_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            PanelAddonCreated::class => 'handleCreated',
            PanelAddonUpdated::class => 'handleUpdated',
            PanelAddonDeleted::class => 'handleDeleted',
        ];
    }
}
