<?php

namespace App\Jobs;

use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SoftDeleteOldActivitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $days = config('herkobi.retention.activities.soft_delete_after_days');

        if (!is_int($days) || $days <= 0) {
            return;
        }

        $cutoffDate = now()->subDays($days);

        Activity::query()
            ->whereNotNull('anonymized_at')
            ->where('anonymized_at', '<=', $cutoffDate)
            ->delete();
    }
}
