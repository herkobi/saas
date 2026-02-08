<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AnonymizeOldNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $anonymizeAfterDays = config('herkobi.retention.notifications.anonymize_after_days');

        if (!is_int($anonymizeAfterDays) || $anonymizeAfterDays <= 0) {
            return;
        }

        $now = now();
        $threshold = $now->copy()->subDays($anonymizeAfterDays);

        DB::table('archived_notifications')
            ->whereNull('anonymized_at')
            ->where('archived_at', '<=', $threshold)
            ->update([
                'data' => null,
                'notifiable_type' => null,
                'notifiable_id' => null,
                'anonymized_at' => $now,
            ]);
    }
}
