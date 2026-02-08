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
            // For API / AJAX requests return JSON with 403
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ekip üyelerine izin verilmiyor.'], 403);
            }

            // For normal web requests, redirect back (or to dashboard) with an error flash
            return redirect()->back()->with('error', 'Ekip üyelerine izin verilmiyor.');
        }

        return $next($request);
    }
}
