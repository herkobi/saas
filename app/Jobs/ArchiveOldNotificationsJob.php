<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ArchiveOldNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $archiveAfterDays = config('herkobi.retention.notifications.archive_after_days');

        if (!is_int($archiveAfterDays) || $archiveAfterDays <= 0) {
            return;
        }

        $now = now();
        $threshold = $now->copy()->subDays($archiveAfterDays);

        DB::table('notifications')
            ->where('created_at', '<=', $threshold)
            ->orderBy('created_at')
            ->chunkById(100, function ($notifications) use ($now) {
                foreach ($notifications as $notification) {
                    DB::table('archived_notifications')->insert([
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'notifiable_type' => $notification->notifiable_type,
                        'notifiable_id' => $notification->notifiable_id,
                        'tenant_id' => $notification->tenant_id ?? null,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'archived_at' => $now,
                        'created_at' => $notification->created_at,
                        'updated_at' => $notification->updated_at,
                    ]);
                }

                $ids = collect($notifications)->pluck('id')->all();
                DB::table('notifications')->whereIn('id', $ids)->delete();
            });
    }
}
