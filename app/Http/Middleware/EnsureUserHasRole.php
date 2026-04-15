<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            return response()->json([
                'data' => null,
                'message' => 'Nuk keni leje për këtë veprim.',
                'status' => 403,
            ], 403);
        }

        return $next($request);
    }
}
