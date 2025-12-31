<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAddress
{
    /**
     * Redirect users without an address to onboarding.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->address()->exists()) {
            return $next($request);
        }

        if ($request->routeIs('onboarding.*', 'logout')) {
            return $next($request);
        }

        return redirect()->route('onboarding.index', ['step' => 2]);
    }
}
