<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseTenant
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'gateway',
        'gateway_payment_id',
        'amount',
        'currency',
        'status',
        'addon_id',
        'description',
        'gateway_response',
        'metadata',
        'paid_at',
        'refunded_at',
        'invoiced_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'gateway_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'invoiced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (Model $model) {
            if (empty($model->currency)) {
                $model->currency = CurrencyHelper::defaultCode();
            }
        });
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Logic & Helpers
    |--------------------------------------------------------------------------
    */
    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::COMPLETED && $this->paid_at !== null;
    }

    public function canRefund(): bool
    {
        return $this->status === PaymentStatus::COMPLETED && $this->refunded_at === null;
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function markAsCompleted(?string $gatewayPaymentId = null, ?array $gatewayResponse = null): void
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'gateway_payment_id' => $gatewayPaymentId ?? $this->gateway_payment_id,
            'gateway_response' => $gatewayResponse ?? $this->gateway_response,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(?array $gatewayResponse = null): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
            'gateway_response' => $gatewayResponse ?? $this->gateway_response,
        ]);
    }

    public function markAsRefunded(?array $gatewayResponse = null): void
    {
        $this->update([
            'status' => PaymentStatus::REFUNDED,
            'gateway_response' => $gatewayResponse ?? $this->gateway_response,
            'refunded_at' => now(),
        ]);
    }
}
