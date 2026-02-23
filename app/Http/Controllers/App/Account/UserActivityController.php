<?php

/**
 * Tenant User Activity Controller
 *
 * Handles user activity log retrieval within a tenant context.
 *
 * @package    App\Http\Controllers\App\Account
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App\Account;

use App\Services\App\Account\UserService;
use App\Services\Shared\TenantContextService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;


/**
 * Controller for tenant user activity logs.
 */
class UserActivityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param UserService $userService The user management service
     */
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display a listing of user activities.
     *
     * @param Request $request The request
     * @param string $userId The user ID
     * @return Response|RedirectResponse
     */
    public function index(Request $request, string $userId): Response|RedirectResponse
    {
        $tenant = app(TenantContextService::class)->currentTenant();
        $user = $this->userService->findById($tenant, $userId);

        if (!$user) {
            return redirect()
                ->route('app.users.index')
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        $activities = $this->userService->getUserActivities(
            $tenant,
            $user,
            $request->integer('per_page', 15)
        );

        return Inertia::render('app/Account/Users/Activities', [
            'user' => $user,
            'activities' => $activities,
        ]);
    }
}
