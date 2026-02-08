<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Subscription extends BaseTenant
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plan_price_id',
        'next_plan_price_id',
        'status',
        'custom_price',
        'custom_currency',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'canceled_at',
        'grace_period_ends_at',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'custom_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
    ];

    public function price(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }

    public function nextPrice(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'next_plan_price_id');
    }

    public function getEffectivePrice(): float
    {
        if ($this->custom_price) {
            return (float) $this->custom_price;
        }

        return (float) $this->price->price;
    }

    public function getEffectiveCurrency(): string
    {
        return $this->custom_currency ?? $this->price->currency;
    }

    public function hasCustomPrice(): bool
    {
        return !is_null($this->custom_price);
    }

    public function calculateStatus(): SubscriptionStatus
    {
        if ($this->onTrial()) {
            return SubscriptionStatus::TRIALING;
        }

        if ($this->hasExpired()) {
            if ($this->onGracePeriod()) {
                return SubscriptionStatus::PAST_DUE;
            }
            return SubscriptionStatus::EXPIRED;
        }

        if ($this->canceled_at) {
            return SubscriptionStatus::CANCELED;
        }

        return SubscriptionStatus::ACTIVE;
    }

    public function updateStatus(): void
    {
        $this->update([
            'status' => $this->calculateStatus()
        ]);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function onGracePeriod(): bool
    {
        return $this->hasExpired() &&
               $this->grace_period_ends_at &&
               $this->grace_period_ends_at->isFuture();
    }

    public function hasExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->status->isValid();
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Helpers
    |--------------------------------------------------------------------------
    */
    public function daysUntilExpiry(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function daysUntilGracePeriodExpiry(): int
    {
        if (!$this->grace_period_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->grace_period_ends_at, false));
    }

    public function daysUntilTrialExpiry(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function hasScheduledDowngrade(): bool
    {
        return $this->next_plan_price_id !== null;
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->daysUntilExpiry() <= $days && $this->daysUntilExpiry() > 0;
    }

    public function planPrice(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }

    public function nextPlanPrice(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'next_plan_price_id');
    }

}
