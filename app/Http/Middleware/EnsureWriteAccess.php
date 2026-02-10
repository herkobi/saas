<?php

/**
 * Ensure Write Access Middleware
 *
 * This middleware ensures that the authenticated user has write access.
 * Users with read-only status (DRAFT) cannot perform write operations.
 *
 * @package    App\Http\Middleware
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Shared\TenantContextServiceInterface;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for ensuring user has write access.
 *
 * Checks the authenticated user's status and prevents
 * write operations for users with read-only status.
 */
class EnsureWriteAccess
{
    /**
     * HTTP methods that require write access.
     *
     * @var array<string>
     */
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $this->isWriteRequest($request)) {
            return $next($request);
        }

        if ($user->status->isReadOnly()) {
            return $this->denyWriteAccess($request, 'Hesabınız kısıtlı modda. Yazma işlemi yapamazsınız.');
        }

        $tenant = app(TenantContextServiceInterface::class)->currentTenant();

        if ($tenant) {
            $pivotStatus = $tenant->users()
                ->where('users.id', $user->id)
                ->first()
                ?->pivot
                ?->status;

            if ($pivotStatus !== null && UserStatus::from((int) $pivotStatus)->isReadOnly()) {
                return $this->denyWriteAccess($request, 'Bu hesaptaki erişiminiz kısıtlı modda. Yazma işlemi yapamazsınız.');
            }
        }

        return $next($request);
    }

    private function denyWriteAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->with('error', $message);
    }

    /**
     * Check if the request is a write request.
     *
     * @param Request $request
     * @return bool
     */
    private function isWriteRequest(Request $request): bool
    {
        return in_array(strtoupper($request->method()), self::WRITE_METHODS);
    }
}
