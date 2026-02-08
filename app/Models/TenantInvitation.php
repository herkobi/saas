<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Enums\TenantUserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantInvitation extends BaseTenant
{
    use HasUlids, SoftDeletes;

    protected $table = 'tenant_invitations';

    protected $fillable = [
        'tenant_id',
        'email',
        'role',
        'token',
        'status',
        'invited_by',
        'accepted_by',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'role' => TenantUserRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::PENDING);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::PENDING;
    }
}
