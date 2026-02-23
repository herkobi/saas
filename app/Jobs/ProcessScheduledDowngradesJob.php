<?php

namespace App\Jobs;

use App\Services\App\Account\SubscriptionLifecycleService;
use App\Notifications\App\Account\PlanDowngradedNotification;
use App\Notifications\App\Account\PlanUpgradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledDowngradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(SubscriptionLifecycleService $service): void
    {
        $subscriptions = $service->getSubscriptionsWithScheduledDowngrade();

        foreach ($subscriptions as $subscription) {
            $oldPlanPriceId = $subscription->plan_price_id;
            $newPlanPriceId = $subscription->next_plan_price_id;
            $isUpgrade = (float) $subscription->nextPrice->price > (float) $subscription->price->price;

            $service->processScheduledDowngrade($subscription);

            $owner = $subscription->tenant?->owner();
            if (!$owner) {
                continue;
            }

            $freshSubscription = $subscription->fresh();

            if ($isUpgrade) {
                $owner->notify(
                    new PlanUpgradedNotification(
                        $freshSubscription,
                        $oldPlanPriceId,
                        $newPlanPriceId
                    )
                );
            } else {
                $owner->notify(
                    new PlanDowngradedNotification(
                        $freshSubscription,
                        $oldPlanPriceId,
                        $newPlanPriceId,
                        true
                    )
                );
            }
        }
    }
}
