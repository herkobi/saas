<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantUserRole;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\DatabaseNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasUlids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'user_type',
        'title',
        'bio',
        'last_login_at',
        'anonymized_at',
    ];


    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'status' => UserStatus::class,
        'user_type' => UserType::class,
        'last_login_at' => 'datetime',
        'anonymized_at' => 'datetime',
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function readNotifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->whereNotNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | User Type Checks
    |--------------------------------------------------------------------------
    */
    public function isPanel(): bool
    {
        return $this->user_type->isAdmin();
    }

    public function isTenant(): bool
    {
        return $this->user_type->isTenant();
    }

    public function isTenantOwner(?Tenant $tenant = null): bool
    {
        if ($this->isPanel()) {
            return true;
        }

        $query = $this->tenants()->wherePivot('role', TenantUserRole::OWNER->value);

        if ($tenant) {
            $query->where('tenants.id', $tenant->id);
        }

        return $query->exists();
    }

    public function isTenantStaff(?Tenant $tenant = null): bool
    {
        if ($this->isPanel()) {
            return true;
        }

        $query = $this->tenants()->wherePivot('role', TenantUserRole::STAFF->value);

        if ($tenant) {
            $query->where('tenants.id', $tenant->id);
        }

        return $query->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Anonymization
    |--------------------------------------------------------------------------
    */
    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }
}
