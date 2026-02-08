<?php

namespace App\Actions\Fortify;

use App\Contracts\App\Account\InvitationServiceInterface;
use App\Contracts\Shared\TenantContextServiceInterface;
use App\Enums\InvitationStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantUserRole;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Events\TenantRegistered;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private readonly TenantContextServiceInterface $tenantContextService
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $existingUser = User::query()
            ->where('email', $input['email'] ?? null)
            ->first();

        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => $this->passwordRules(),
        ]);

        $validator->after(function ($validator) use ($existingUser): void {
            if ($existingUser && ! $this->tenantContextService->canCreateNewTenant($existingUser)) {
                $validator->errors()->add('email', 'Bu hesap zaten bir işletmeye sahip. Birden fazla işletme oluşturma izniniz yok.');
            }
        });

        $validator->validate();

        $user = DB::transaction(function () use ($input, $existingUser) {
            $user = $existingUser ?: User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'status' => UserStatus::ACTIVE,
                'user_type' => UserType::TENANT,
            ]);

            $code = $this->generateUniqueTenantCode();

            $tenant = Tenant::create([
                'code' => $code,
                'slug' => Str::slug($input['name'].'-'.$code),
                'status' => SubscriptionStatus::ACTIVE,
                'account' => [
                    'title' => $input['name'],
                ],
            ]);

            $tenant->users()->attach($user->id, [
                'role' => TenantUserRole::OWNER->value,
                'joined_at' => now(),
            ]);

            TenantRegistered::dispatch(
                $user,
                $tenant,
                Request::ip() ?? '127.0.0.1',
                Request::userAgent() ?? 'unknown'
            );

            return $user;
        });

        $this->acceptPendingInvitations($user);

        return $user;
    }

    private function acceptPendingInvitations(User $user): void
    {
        $pendingInvitations = TenantInvitation::withoutTenantScope()
            ->where('email', $user->email)
            ->where('status', InvitationStatus::PENDING)
            ->where('expires_at', '>', now())
            ->get();

        if ($pendingInvitations->isEmpty()) {
            return;
        }

        /** @var InvitationServiceInterface $invitationService */
        $invitationService = app(InvitationServiceInterface::class);

        /** @var TenantInvitation $invitation */
        foreach ($pendingInvitations as $invitation) {
            try {
                $invitationService->acceptInvitationDirectly($invitation, $user);
            } catch (\Throwable) {
                // Silently skip — user can accept manually later
            }
        }
    }

    protected function generateUniqueTenantCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (Tenant::where('code', $code)->exists());

        return $code;
    }
}
