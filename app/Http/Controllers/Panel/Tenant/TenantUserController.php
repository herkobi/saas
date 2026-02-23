<?php

/**
 * Panel Tenant User Controller
 *
 * This controller handles tenant user listing operations for the panel,
 * providing read-only access to tenant user information.
 *
 * @package    App\Http\Controllers\Panel\Tenant
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Tenant;

use App\Services\Panel\Tenant\TenantService;
use App\Enums\UserStatus;
use App\Events\PanelUserStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Tenant\UpdateUserStatusRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for panel tenant user viewing.
 *
 * Provides methods for listing tenant users with role and
 * membership information.
 */
class TenantUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantService $tenantService Service for tenant operations
     */
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    /**
     * Display a listing of users for a tenant.
     *
     * @param Tenant $tenant
     * @return View
     */
    public function index(Tenant $tenant): Response
    {
        $users = $this->tenantService->getUsers($tenant);
        $statistics = $this->tenantService->getStatistics($tenant);

        return Inertia::render('panel/Tenants/Users', [
            'tenant' => $tenant,
            'users' => $users,
            'statistics' => $statistics,
            'statusOptions' => collect(UserStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->toArray(),
        ]);
    }

    /**
     * Manually update the status of a user.
     *
     * This is an admin override for exceptional cases (ban, suspend, activate).
     *
     * @param UpdateUserStatusRequest $request
     * @param Tenant $tenant
     * @param User $user
     * @return RedirectResponse
     */
    public function updateStatus(UpdateUserStatusRequest $request, Tenant $tenant, User $user): RedirectResponse
    {
        $oldStatus = $user->status;
        $newStatus = UserStatus::from($request->validated('status'));

        $user->update([
            'status' => $newStatus,
        ]);

        PanelUserStatusUpdated::dispatch(
            $user,
            $oldStatus,
            $newStatus,
            $request->validated('reason'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return redirect()
            ->route('panel.tenants.users.index', $tenant)
            ->with('success', 'Kullanıcı durumu başarıyla güncellendi.');
    }
}
