<?php

/**
 * Handle Inertia Requests Middleware
 *
 * This middleware handles Inertia.js requests by sharing common data
 * across all Inertia responses (auth user, flash messages, etc.).
 *
 * @package     App\Http\Middleware
 * @author      Herkobi
 * @copyright   2025 Herkobi
 * @license     MIT
 * @version     1.0.0
 * @since       1.0.0
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Shared\TenantContextServiceInterface;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Middleware for handling Inertia.js requests.
 *
 * Shares common data like authenticated user, flash messages,
 * and tenant context across all Inertia responses.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $tenantContextService = app(TenantContextServiceInterface::class);
        $tenant = $tenantContextService->currentTenant();

        return array_merge(parent::share($request), [

            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],

            'site' => [
                'name' => settings('site_name', config('app.name')),
                'logo' => logo(),
                'favicon' => favicon(),
                'allow_team_members' => config('herkobi.tenant.allow_team_members', false),
                'allow_multiple_tenants' => config('herkobi.tenant.allow_multiple_tenants', false),
            ],

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->user_type->value,
                    'status' => $user->status->value,
                    'two_factor_enabled' => !is_null($user->two_factor_confirmed_at),
                ] : null,
                'tenants' => $user && $tenantContextService->multipleTenantsAllowed()
                    ? $user->tenants()->withPivot(['role', 'status', 'joined_at'])->orderByPivot('joined_at')->get()->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->account['title'] ?? $t->name,
                        'role' => $t->pivot->role,
                        'status' => $t->pivot->status,
                    ])->toArray()
                    : [],
                'can_create_tenant' => $user
                    ? $tenantContextService->canCreateNewTenant($user)
                    : false,
            ],

            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'account' => $tenant->account,
            ] : null,
        ]);
    }
}
