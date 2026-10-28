<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Allow only administrators through. Customers keep read-only access
     * to the catalog.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Administrator access is required.',
                ], 403);
            }

            abort(403, 'Administrator access is required.');
        }

        return $next($request);
    }
}
