<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AddonType;
use App\Enums\PlanInterval;
use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'feature_id',
        'addon_type',
        'value',
        'price',
        'currency',
        'is_recurring',
        'interval',
        'interval_count',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'addon_type' => AddonType::class,
        'interval' => PlanInterval::class,
        'interval_count' => 'integer',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_addons')
            ->using(TenantAddon::class)
            ->withPivot([
                'id',
                'quantity',
                'custom_price',
                'custom_currency',
                'started_at',
                'expires_at',
                'is_active',
                'metadata'
            ])
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOneTime(Builder $query): Builder
    {
        return $query->where('is_recurring', false);
    }

    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    public function isOneTime(): bool
    {
        return !$this->is_recurring;
    }

    public function getFormattedPrice(): string
    {
        return CurrencyHelper::format(
            (float) $this->price
        );
    }

    public function getEffectivePrice(?float $customPrice = null): float
    {
        return $customPrice ?? (float) $this->price;
    }
}
