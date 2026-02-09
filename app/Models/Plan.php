<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GracePeriod;
use App\Enums\ProrationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'tenant_id',
        'is_free',
        'is_active',
        'is_public',
        'sort_order',
        'archived_at',
        'grace_period_days',
        'grace_period_policy',
        'upgrade_proration_type',
        'downgrade_proration_type',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = \Illuminate\Support\Str::slug($plan->name);
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty('name') && empty($plan->slug)) {
                $plan->slug = \Illuminate\Support\Str::slug($plan->name);
            }
        });
    }

    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
        'archived_at' => 'datetime',
        'grace_period_days' => 'integer',
        'grace_period_policy' => GracePeriod::class,
        'upgrade_proration_type' => ProrationType::class,
        'downgrade_proration_type' => ProrationType::class,
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['value'])
            ->withTimestamps();
    }

    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Subscription::class,
            PlanPrice::class,
            'plan_id',
            'plan_price_id',
            'id',
            'id'
        );
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('tenant_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_free', false);
    }

    public function isGlobal(): bool
    {
        return is_null($this->tenant_id);
    }

    public function isTenantSpecific(): bool
    {
        return !is_null($this->tenant_id);
    }

    public function isFree(): bool
    {
        return $this->is_free;
    }

    public function isPaid(): bool
    {
        return !$this->is_free;
    }

    public function resolveUpgradeProration(): ProrationType
    {
        return $this->upgrade_proration_type
            ?? ProrationType::from(config('herkobi.proration.upgrade_behavior', 'immediate'));
    }

    public function resolveDowngradeProration(): ProrationType
    {
        return $this->downgrade_proration_type
            ?? ProrationType::from(config('herkobi.proration.downgrade_behavior', 'end_of_period'));
    }
}
