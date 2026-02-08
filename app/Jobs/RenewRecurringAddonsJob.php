<?php

namespace App\Jobs;

use App\Enums\CheckoutType;
use App\Models\TenantAddon;
use App\Notifications\App\Account\AddonExpiringNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Renew recurring addons that are about to expire.
 *
 * This job is gated behind config('herkobi.addon.auto_renew').
 * When auto_renew is false (default), expiring addons are handled by
 * CheckExpiredAddonsJob (deactivation) and SendAddonExpiryReminderJob (notifications).
 *
 * When auto_renew is true, this job will create checkout sessions for renewal.
 * Requires stored card/token payment support from the gateway.
 */
class RenewRecurringAddonsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        if (!config('herkobi.addon.auto_renew', false)) {
            return;
        }

        // Auto-renewal requires stored card/token payment support.
        // This is a placeholder for future implementation when PayTR
        // or another gateway supports tokenized recurring payments.
        //
        // Implementation flow:
        // 1. Find active recurring addons expiring today
        // 2. For each: create ADDON_RENEW checkout
        // 3. Charge via stored card token
        // 4. On success: extend expires_at
        // 5. On failure: deactivate + notify owner

        TenantAddon::query()
            ->with(['tenant', 'addon'])
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '=', now()->toDateString())
            ->whereHas('addon', fn ($q) => $q->where('is_recurring', true))
            ->get()
            ->each(function (TenantAddon $tenantAddon): void {
                $owner = $tenantAddon->tenant?->owner();

                if (!$owner) {
                    return;
                }

                // Until stored card support is available,
                // notify the owner to manually renew
                $owner->notify(
                    new AddonExpiringNotification($tenantAddon->addon, $tenantAddon, 0)
                );
            });
    }
}
