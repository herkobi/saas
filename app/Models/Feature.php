<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeatureType;
use App\Enums\ResetPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'code',
        'slug',
        'name',
        'description',
        'type',
        'unit',
        'reset_period',
        'is_active',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($feature) {
            if (empty($feature->slug)) {
                $feature->slug = \Illuminate\Support\Str::slug($feature->name);
            }
        });

        static::updating(function ($feature) {
            if ($feature->isDirty('name') && empty($feature->slug)) {
                $feature->slug = \Illuminate\Support\Str::slug($feature->name);
            }
        });
    }

    protected $casts = [
        'type' => FeatureType::class,
        'reset_period' => ResetPeriod::class,
        'is_active' => 'boolean',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot(['value'])
            ->withTimestamps();
    }

    public function addons(): HasMany
    {
        return $this->hasMany(Addon::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(TenantUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isMetered(): bool
    {
        return $this->type === FeatureType::METERED;
    }

    public function isLimit(): bool
    {
        return $this->type === FeatureType::LIMIT;
    }

    public function isFeature(): bool
    {
        return $this->type === FeatureType::FEATURE;
    }
}
