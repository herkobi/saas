<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class TenantFeature extends BaseTenant
{
    use HasUlids;

    protected $fillable = [
        'feature_id',
        'value',
    ];

    public function scopeForTenant(Builder $query, Tenant|string $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query
            ->withoutTenantScope()
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }


    public static function queryForTenant(Tenant|string $tenant): Builder
    {
        return static::query()->forTenant($tenant);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
