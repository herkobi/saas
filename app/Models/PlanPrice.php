<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanInterval;
use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPrice extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'price',
        'currency',
        'interval',
        'interval_count',
        'trial_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'interval' => PlanInterval::class,
        'interval_count' => 'integer',
        'trial_days' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_price_id');
    }
    
    public function getFormattedPrice(): string
    {
        return CurrencyHelper::format((float) $this->price);
    }
}
