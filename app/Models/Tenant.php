<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AddonType;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantUserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'code',
        'slug',
        'domain',
        'account',
        'data',
        'status',
    ];

    protected $casts = [
        'account' => 'array',
        'data' => 'array',
        'status' => SubscriptionStatus::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'tenant_addons')
            ->using(TenantAddon::class)
            ->withPivot([
                'quantity',
                'custom_price',
                'custom_currency',
                'started_at',
                'expires_at',
                'is_active',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function owner(): ?User
    {
        return $this->users()
            ->wherePivot('role', TenantUserRole::OWNER->value)
            ->first();
    }

    public function isOwner(User $user): bool
    {
        return $this->users()
            ->wherePivot('role', TenantUserRole::OWNER->value)
            ->where('users.id', $user->id)
            ->exists();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function currentSubscription(): ?Subscription
    {
        $subscription = $this->subscription;

        if ($subscription && $subscription->isValid()) {
            return $subscription;
        }

        return null;
    }

    public function currentPlan(): ?Plan
    {
        return $this->currentSubscription()?->price?->plan;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(TenantFeature::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(TenantUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('subscription', function (Builder $q) {
            $q->whereIn('status', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::TRIALING,
                SubscriptionStatus::CANCELED,
                SubscriptionStatus::PAST_DUE,
            ]);
        });
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->currentSubscription();

        return $subscription && $subscription->isValid();
    }

    public function getFeatureLimit(Feature $feature): ?float
    {

        $override = $this->featureOverrides()
            ->where('feature_id', $feature->id)
            ->first();

        if ($override) {
            return $override->value === null
                ? null
                : (float) $override->value;
        }

        $subscription = $this->subscription;
        if (! $subscription) {
            return null;
        }

        $planFeature = $subscription->price->plan->features()
            ->where('feature_id', $feature->id)
            ->first();

        if (! $planFeature) {
            return null;
        }

        $planValue = $planFeature->pivot->value;

        if ($planValue === null) {
            return null;
        }

        $addons = $this->addons()
            ->where('feature_id', $feature->id)
            ->where('tenant_addons.is_active', true)
            ->where(function ($q) {
                $q->whereNull('tenant_addons.expires_at')
                    ->orWhere('tenant_addons.expires_at', '>', now());
            })
            ->get();

        foreach ($addons as $addon) {

            if ($addon->addon_type === AddonType::UNLIMITED) {
                return null;
            }

            if ($addon->addon_type === AddonType::INCREMENT) {
                $addonValue = (int) $addon->value * $addon->pivot->quantity;
                $planValue = (int) $planValue + $addonValue;
            }
        }

        return (float) $planValue;
    }

    public function hasFeatureAccess(Feature $feature): bool
    {

        $override = $this->featureOverrides()
            ->where('feature_id', $feature->id)
            ->first();

        if ($override) {
            return (bool) $override->value;
        }

        $subscription = $this->subscription;
        if (! $subscription) {
            return false;
        }

        $planFeature = $subscription->price->plan->features()
            ->where('feature_id', $feature->id)
            ->first();

        if (! $planFeature) {
            return false;
        }

        $hasAccess = (bool) $planFeature->pivot->value;

        $booleanAddons = $this->addons()
            ->where('feature_id', $feature->id)
            ->where('addon_type', AddonType::BOOLEAN)
            ->where('tenant_addons.is_active', true)
            ->where(function ($q) {
                $q->whereNull('tenant_addons.expires_at')
                    ->orWhere('tenant_addons.expires_at', '>', now());
            })
            ->exists();

        return $hasAccess || $booleanAddons;
    }
}
