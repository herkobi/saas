<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;


class PlanFeature extends Pivot
{
    use HasUlids;

    protected $table = 'plan_features';

    public $timestamps = true;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'plan_id',
        'feature_id',
        'value',
    ];
}
