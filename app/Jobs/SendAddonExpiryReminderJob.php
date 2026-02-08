<?php

namespace App\Jobs;

use App\Models\TenantAddon;
use App\Notifications\App\Account\AddonExpiringNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAddonExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $daysList = config('herkobi.addon.expiry_reminder_days');

        if (!is_array($daysList) || $daysList === []) {
            return;
        }

        $daysList = array_values(array_unique(array_filter($daysList, fn ($day) => is_int($day) && $day > 0)));
        if ($daysList === []) {
            return;
        }

        $today = now()->startOfDay();

        foreach ($daysList as $days) {
            $targetDate = $today->copy()->addDays($days);

            TenantAddon::query()
                ->with(['tenant', 'addon.feature'])
                ->where('is_active', true)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '=', $targetDate->toDateString())
                ->get()
                ->each(function (TenantAddon $tenantAddon) use ($days): void {
                    $owner = $tenantAddon->tenant?->owner();

                    if (!$owner) {
                        return;
                    }

                    $alreadySent = $owner->notifications()
                        ->where('type', AddonExpiringNotification::class)
                        ->where('data->addon_id', $tenantAddon->addon_id)
                        ->where('data->days_remaining', $days)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        return;
                    }

                    $owner->notify(
                        new AddonExpiringNotification($tenantAddon->addon, $tenantAddon, $days)
                    );
                });
        }
    }
}
