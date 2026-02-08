<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class TenantUsage extends BaseTenant
{
    use HasUlids;

    protected $fillable = [
        'feature_id',
        'used',
        'cycle_ends_at',
    ];

    protected $casts = [
        'used' => 'decimal:2',
        'cycle_ends_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
