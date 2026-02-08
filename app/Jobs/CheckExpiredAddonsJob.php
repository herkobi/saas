<?php

namespace App\Jobs;

use App\Events\TenantAddonExpired;
use App\Models\TenantAddon;
use App\Notifications\App\Account\AddonExpiredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckExpiredAddonsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $cacheTtlDays = 7;

        TenantAddon::query()
            ->with(['tenant', 'addon.feature'])
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get()
            ->each(function (TenantAddon $tenantAddon) use ($cacheTtlDays): void {
                $cacheKey = "addon_expired:{$tenantAddon->id}";

                if (Cache::has($cacheKey)) {
                    return;
                }

                $tenantAddon->update(['is_active' => false]);

                TenantAddonExpired::dispatch($tenantAddon->tenant, $tenantAddon);

                $owner = $tenantAddon->tenant?->owner();
                if ($owner) {
                    $owner->notify(
                        new AddonExpiredNotification($tenantAddon->addon, $tenantAddon)
                    );
                }

                Cache::put($cacheKey, true, now()->addDays($cacheTtlDays));
            });
    }
}
