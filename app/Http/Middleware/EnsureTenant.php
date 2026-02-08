<?php

/**
 * Ensure Tenant Middleware
 *
 * This middleware ensures that the authenticated user is a tenant user.
 * Panel users are redirected to the panel dashboard.
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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for ensuring tenant user access.
 *
 * Verifies that the authenticated user has tenant user type.
 * Panel users are redirected to the appropriate panel area.
 */
class EnsureTenant
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if (!$user->isTenant()) {
            return redirect()->route('panel.dashboard');
        }

        return $next($request);
    }
}
