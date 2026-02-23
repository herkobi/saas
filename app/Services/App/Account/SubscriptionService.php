<?php

declare(strict_types=1);

namespace App\Services\App\Account;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;

class SubscriptionService
{
    public function getCurrentSubscription(Tenant $tenant): ?Subscription
    {
        return $tenant->subscription;
    }

    public function getSubscriptionDetails(Tenant $tenant): array
    {
        $subscription = $this->getCurrentSubscription($tenant);

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'subscription' => null,
                'plan' => null,
                'price' => null,
            ];
        }

        $price = $subscription->price;
        $plan = $price?->plan;

        return [
            'has_subscription' => true,
            'subscription' => [
                'id' => $subscription->id,
                'status' => [
                    'value' => $subscription->status->value,
                    'label' => $subscription->status->label(),
                    'badge' => $subscription->status->badge(),
                ],
                'starts_at' => $subscription->starts_at?->toISOString(),
                'ends_at' => $subscription->ends_at?->toISOString(),
                'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
                'canceled_at' => $subscription->canceled_at?->toISOString(),
                'grace_period_ends_at' => $subscription->grace_period_ends_at?->toISOString(),
                'on_trial' => $subscription->onTrial(),
                'on_grace_period' => $subscription->onGracePeriod(),
                'has_expired' => $subscription->hasExpired(),
                'is_valid' => $subscription->isValid(),
                'days_remaining' => $this->getDaysRemaining($tenant),
                'custom_price' => $subscription->custom_price,
                'custom_currency' => $subscription->custom_currency,
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
            ] : null,
            'price' => $price ? [
                'id' => $price->id,
                'amount' => $price->price,
                'currency' => $price->currency,
                'interval' => $price->interval->value,
                'interval_label' => $price->interval->label(),
                'interval_count' => $price->interval_count,
                'formatted' => $price->formatted_price ?? null,
            ] : null,
            'next_plan' => $subscription->nextPrice ? [
                'name' => $subscription->nextPrice->plan?->name,
                'effective_date' => $subscription->ends_at?->toISOString(),
            ] : null,
        ];
    }

    public function getPlanFeatures(Tenant $tenant): array
    {
        $subscription = $this->getCurrentSubscription($tenant);

        if (!$subscription) {
            return [];
        }

        $plan = $subscription->price?->plan;

        if (!$plan) {
            return [];
        }

        return $plan->features->map(function ($feature) use ($tenant) {
            $override = $tenant->featureOverrides()
                ->where('feature_id', $feature->id)
                ->first();

            return [
                'id' => $feature->id,
                'name' => $feature->name,
                'slug' => $feature->slug,
                'description' => $feature->description,
                'type' => $feature->type->value,
                'type_label' => $feature->type->label(),
                'unit' => $feature->unit,
                'plan_value' => $feature->pivot->value,
                'effective_value' => $override?->value ?? $feature->pivot->value,
                'has_override' => $override !== null,
            ];
        })->toArray();
    }

    public function getAvailablePlans(): array
    {
        return Plan::where('is_active', true)
            ->where('is_public', true)
            ->whereNull('archived_at')
            ->with(['prices' => function ($query) {
                $query->where('is_active', true)->orderBy('price');
            }, 'features'])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'prices' => $plan->prices->map(fn($p) => [
                        'id' => $p->id,
                        'amount' => $p->price,
                        'currency' => $p->currency,
                        'interval' => $p->interval->value,
                        'interval_label' => $p->interval->label(),
                        'interval_count' => $p->interval_count,
                        'trial_days' => $p->trial_days,
                    ]),
                    'features' => $plan->features->map(fn($f) => [
                        'name' => $f->name,
                        'value' => $f->pivot->value,
                        'type' => $f->type->value,
                    ]),
                ];
            })
            ->toArray();
    }

    public function canUpgrade(Tenant $tenant): bool
    {
        $subscription = $this->getCurrentSubscription($tenant);

        if (!$subscription) {
            return true;
        }

        return $subscription->isValid() && !$subscription->canceled_at;
    }

    public function canDowngrade(Tenant $tenant): bool
    {
        $subscription = $this->getCurrentSubscription($tenant);

        if (!$subscription) {
            return false;
        }

        return $subscription->isValid() && !$subscription->canceled_at;
    }

    public function getDaysRemaining(Tenant $tenant): ?int
    {
        $subscription = $this->getCurrentSubscription($tenant);

        if (!$subscription || !$subscription->ends_at) {
            return null;
        }

        $days = now()->diffInDays($subscription->ends_at, false);

        return max(0, (int) $days);
    }

    public function isOnTrial(Tenant $tenant): bool
    {
        $subscription = $this->getCurrentSubscription($tenant);

        return $subscription?->onTrial() ?? false;
    }

    public function isOnGracePeriod(Tenant $tenant): bool
    {
        $subscription = $this->getCurrentSubscription($tenant);

        return $subscription?->onGracePeriod() ?? false;
    }
}
