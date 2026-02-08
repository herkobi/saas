<?php

namespace App\Jobs;

use App\Events\TenantSubscriptionExpired;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckExpiredSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $cacheTtlDays = 7;

        Subscription::query()
            ->with(['tenant.owner', 'price.plan'])
            ->whereDate('ends_at', $yesterday)
            ->whereNull('canceled_at')
            ->get()
            ->each(function (Subscription $subscription) use ($cacheTtlDays): void {
                $cacheKey = "subscription_expired:{$subscription->id}";

                if (Cache::has($cacheKey)) {
                    return;
                }

                TenantSubscriptionExpired::dispatch($subscription);

                Cache::put($cacheKey, true, now()->addDays($cacheTtlDays));
            });
    }
}
