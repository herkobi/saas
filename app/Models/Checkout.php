<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
use App\Helpers\CurrencyHelper;
use App\Services\Shared\TenantContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkout extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plan_price_id',
        'addon_id',
        'quantity',
        'payment_id',
        'merchant_oid',
        'type',
        'status',
        'amount',
        'proration_credit',
        'final_amount',
        'currency',
        'paytr_token',
        'billing_info',
        'metadata',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'type' => CheckoutType::class,
        'status' => CheckoutStatus::class,
        'quantity' => 'integer',
        'billing_info' => 'array',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->tenant_id)) {
                $tenant = app(TenantContextService::class)->currentTenant();
                if ($tenant instanceof Tenant) {
                    $model->tenant_id = $tenant->id;
                }
            }

            if (empty($model->currency)) {
                $model->currency = CurrencyHelper::defaultCode();
            }
        });
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenant = app(TenantContextService::class)->currentTenant();

        if ($tenant instanceof Tenant) {
            return $query->where($this->getTable() . '.tenant_id', $tenant->id);
        }

        return $query;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CheckoutStatus::PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', CheckoutStatus::COMPLETED);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function planPrice(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === CheckoutStatus::COMPLETED;
    }

    public function canProcess(): bool
    {
        return $this->status->canProcess();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function isExpired(): bool
    {
        return $this->status === CheckoutStatus::EXPIRED ||
               ($this->status === CheckoutStatus::PENDING && $this->expires_at?->isPast());
    }

    public function markAsProcessing(string $token): void
    {
        $this->update([
            'status' => CheckoutStatus::PROCESSING,
            'paytr_token' => $token,
        ]);
    }

    public function markAsCompleted(?string $paymentId = null): void
    {
        $this->update([
            'status' => CheckoutStatus::COMPLETED,
            'payment_id' => $paymentId ?? $this->payment_id,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => CheckoutStatus::FAILED]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => CheckoutStatus::EXPIRED]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => CheckoutStatus::CANCELLED]);
    }

    public static function generateMerchantOid(string $tenantId): string
    {
        return \sprintf(
            '%s_%s_%s',
            config('app.name', 'HRK'),
            substr($tenantId, 0, 8),
            Str::ulid()
        );
    }
}
