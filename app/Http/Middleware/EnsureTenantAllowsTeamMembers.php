<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Ensure the tenant configuration allows team members.
 */
class EnsureTenantAllowsTeamMembers
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('herkobi.tenant.allow_team_members') !== true) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bu özellik aktif değil.'], 422);
            }

            return redirect()->route('app.dashboard')->with('error', 'Bu özellik aktif değil.');
        }

        return $next($request);
    }
}
