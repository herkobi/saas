<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Notifications\App\Account\SubscriptionRenewalReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSubscriptionRenewalReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $daysList = config('herkobi.subscription.renewal_reminder_days');

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

            Subscription::query()
                ->whereNotNull('ends_at')
                ->whereIn('status', [
                    SubscriptionStatus::ACTIVE,
                    SubscriptionStatus::PAST_DUE,
                ])
                ->whereDate('ends_at', '=', $targetDate->toDateString())
                ->get()
                ->each(function (Subscription $subscription) use ($days): void {
                    $owner = $subscription->tenant?->owner;

                    if (!$owner) {
                        return;
                    }

                    $alreadySent = $owner->notifications()
                        ->where('type', SubscriptionRenewalReminderNotification::class)
                        ->where('data->subscription_id', $subscription->id)
                        ->where('data->days_remaining', $days)
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if ($alreadySent) {
                        return;
                    }

                    $owner->notify(
                        new SubscriptionRenewalReminderNotification($subscription, $days)
                    );
                });
        }
    }
}
