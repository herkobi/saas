<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Shared\ActivityServiceInterface;
use App\Events\TenantAddonCancelled;
use App\Events\TenantAddonExpired;
use App\Events\TenantAddonPurchased;
use Illuminate\Events\Dispatcher;

class LogTenantAddonActivity
{
    public function __construct(
        protected ActivityServiceInterface $activityService
    ) {}

    /**
     * Handle addon purchased event.
     */
    public function handlePurchased(TenantAddonPurchased $event): void
    {
        $this->activityService->log(
            user: $event->tenant->owner(),
            type: 'tenant.addon_purchased',
            description: 'Eklenti satın alındı',
            log: [
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'addon_id' => $event->tenantAddon->addon_id,
                'addon_name' => $event->tenantAddon->addon->name,
                'addon_type' => $event->tenantAddon->addon->addon_type->value,
                'quantity' => $event->tenantAddon->quantity,
                'price' => $event->tenantAddon->getTotalPrice(),
                'currency' => $event->tenantAddon->getEffectiveCurrency(),
                'started_at' => $event->tenantAddon->started_at?->toDateTimeString(),
                'expires_at' => $event->tenantAddon->expires_at?->toDateTimeString(),
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'purchased_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle addon expired event.
     */
    public function handleExpired(TenantAddonExpired $event): void
    {
        $this->activityService->log(
            user: $event->tenant->owner(),
            type: 'tenant.addon_expired',
            description: 'Eklenti süresi doldu',
            log: [
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'addon_id' => $event->tenantAddon->addon_id,
                'addon_name' => $event->tenantAddon->addon->name,
                'addon_type' => $event->tenantAddon->addon->addon_type->value,
                'quantity' => $event->tenantAddon->quantity,
                'expired_at' => $event->tenantAddon->expires_at?->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle addon cancelled event.
     */
    public function handleCancelled(TenantAddonCancelled $event): void
    {
        $this->activityService->log(
            user: $event->tenant->owner(),
            type: 'tenant.addon_cancelled',
            description: 'Eklenti iptal edildi',
            log: [
                'tenant_id' => $event->tenant->id,
                'tenant_code' => $event->tenant->code,
                'addon_id' => $event->tenantAddon->addon_id,
                'addon_name' => $event->tenantAddon->addon->name,
                'addon_type' => $event->tenantAddon->addon->addon_type->value,
                'quantity' => $event->tenantAddon->quantity,
                'ip_address' => $event->ip,
                'user_agent' => $event->userAgent,
                'cancelled_at' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TenantAddonPurchased::class => 'handlePurchased',
            TenantAddonExpired::class => 'handleExpired',
            TenantAddonCancelled::class => 'handleCancelled',
        ];
    }
}
