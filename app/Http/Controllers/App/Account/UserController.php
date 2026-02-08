<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Account;

use App\Contracts\App\Account\FeatureUsageServiceInterface;
use App\Contracts\App\Account\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Account\UpdateUserRoleRequest;
use App\Traits\HasTenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use HasTenantContext;

    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly FeatureUsageServiceInterface $featureUsageService
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $users = $this->userService->getPaginated(
            $tenant,
            $request->all()
        );

        return Inertia::render('app/Account/Users/Index', compact('users'));
    }

    public function show(string $userId): Response|RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $user = $this->userService->findById($tenant, $userId);

        if (! $user) {
            return redirect()
                ->route('account.users.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Kullanıcı bulunamadı.',
                ]);
        }

        return Inertia::render('app/Account/Users/Show', compact('user'));
    }

    public function updateRole(UpdateUserRoleRequest $request, string $userId): RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('app.dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $currentUser = $request->user();

        $targetUser = $this->userService->findById($tenant, $userId);

        if (! $targetUser) {
            return redirect()
                ->route('account.users.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Kullanıcı bulunamadı.',
                ]);
        }

        if (! $this->userService->canChangeRole(
            $currentUser,
            $targetUser,
            $tenant,
            (int) $request->role
        )) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Bu işlem için yetkiniz yok.',
                ]);
        }

        $this->userService->changeRole(
            $tenant,
            $targetUser,
            (int) $request->role,
            $currentUser,
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'unknown'
        );

        return redirect()
            ->back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Kullanıcı rolü güncellendi.',
            ]);
    }

    public function remove(Request $request, string $userId): RedirectResponse
    {
        $tenant = $this->currentTenant();

        if (! $tenant) {
            return redirect()
                ->route('dashboard')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Tenant bulunamadı.',
                ]);
        }

        $currentUser = $request->user();

        $targetUser = $this->userService->findById($tenant, $userId);

        if (! $targetUser) {
            return redirect()
                ->route('account.users.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Kullanıcı bulunamadı.',
                ]);
        }

        if (! $this->userService->canManageUser(
            $currentUser,
            $targetUser,
            $tenant
        )) {
            return redirect()
                ->back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Bu işlem için yetkiniz yok.',
                ]);
        }

        $this->userService->removeUser(
            $tenant,
            $targetUser,
            $currentUser,
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'unknown'
        );

        $this->featureUsageService->decrementUsage($tenant, 'users');

        return redirect()
            ->back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Kullanıcı tenant\'tan çıkarıldı.',
            ]);
    }
}
