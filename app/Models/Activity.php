<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserType;
use App\Helpers\MaskHelper;
use App\Services\Shared\TenantContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_type',
        'tenant_id',
        'type',
        'description',
        'log',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'user_type' => UserType::class,
        'log' => 'array',
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

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | KVKK/GDPR Compliance (Masking & Anonymization)
    |--------------------------------------------------------------------------
    */
    public function anonymize(): void
    {
        $log = $this->log;

        if (isset($log['user_name'])) {
            $log['user_name'] = MaskHelper::name((string) $log['user_name']);
        }

        if (isset($log['user_email'])) {
            $log['user_email'] = MaskHelper::email((string) $log['user_email']);
        }

        $this->update([
            'user_id' => null,
            'ip_address' => $this->ip_address ? MaskHelper::ip($this->ip_address) : null,
            'user_agent' => null,
            'log' => $log,
        ]);
    }


    public function isAnonymized(): bool
    {
        return $this->user_id === null && $this->user_agent === null;
    }
}
