<?php

namespace App\Jobs;

use App\Events\TenantTrialEnded;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckTrialExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function handle(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $cacheTtlDays = 7;

        Tenant::query()
            ->whereDate('trial_ends_at', $yesterday)
            ->whereNull('trial_ended_at')
            ->get()
            ->each(function (Tenant $tenant) use ($cacheTtlDays): void {
                $cacheKey = "trial_expired:{$tenant->id}";

                if (Cache::has($cacheKey)) {
                    return;
                }

                TenantTrialEnded::dispatch($tenant);

                Cache::put($cacheKey, true, now()->addDays($cacheTtlDays));
            });
    }
}
