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

        if ($user && $user->status->isReadOnly() && $this->isWriteRequest($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hesabınız kısıtlı modda. Yazma işlemi yapamazsınız.',
                ], Response::HTTP_FORBIDDEN);
            }

            return back()->with('error', 'Hesabınız kısıtlı modda. Yazma işlemi yapamazsınız.');
        }

        return $next($request);
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
