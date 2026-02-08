<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Shared\TenantContextServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class BaseTenant extends Model
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenant = app(TenantContextServiceInterface::class)->currentTenant();

            if ($tenant instanceof Tenant) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->tenant_id)) {
                $tenant = app(TenantContextServiceInterface::class)->currentTenant();

                if ($tenant instanceof Tenant) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }

    public static function withoutTenantScope(): Builder
    {
        return static::query()->withoutGlobalScope('tenant');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
