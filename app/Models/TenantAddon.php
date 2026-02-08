<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Shared\TenantContextServiceInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantAddon extends Pivot
{
    use HasUlids;

    protected $table = 'tenant_addons';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'addon_id',
        'quantity',
        'custom_price',
        'custom_currency',
        'started_at',
        'expires_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'custom_price' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('tenant', function ($builder) {
            $tenant = app(TenantContextServiceInterface::class)->currentTenant();

            if ($tenant instanceof Tenant) {
                $builder->where('tenant_addons.tenant_id', $tenant->id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTotalPrice(): float
    {
        $unitPrice = $this->custom_price ?? $this->addon->price;
        return (float) $unitPrice * $this->quantity;
    }

    public function getEffectiveCurrency(): string
    {
        return $this->custom_currency ?? $this->addon->currency;
    }
}
