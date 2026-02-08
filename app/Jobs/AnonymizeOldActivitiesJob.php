<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\UserType;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnonymizeOldActivitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $anonymizeAfterDays = config('herkobi.retention.activities.anonymize_after_days');

        if (!is_int($anonymizeAfterDays) || $anonymizeAfterDays <= 0) {
            return;
        }

        $now = now();
        $threshold = $now->copy()->subDays($anonymizeAfterDays);

        Activity::query()
            ->where('user_type', UserType::TENANT)
            ->whereNull('anonymized_at')
            ->where('created_at', '<=', $threshold)
            ->update([
                'user_id' => null,
                'ip_address' => null,
                'user_agent' => null,
                'metadata' => null,
                'anonymized_at' => $now,
            ]);
    }
}
