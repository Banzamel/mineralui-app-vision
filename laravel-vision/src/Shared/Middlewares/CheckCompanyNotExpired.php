<?php

namespace Shared\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware checking whether the logged-in user's company subscription has not expired - blocks access when it has.
 */
class CheckCompanyNotExpired
{
    /**
     * Handles the current request - rejects it when the user's company has an expired subscription, otherwise passes it through.
     *
     * @param mixed $request Current HTTP request.
     * @param Closure $next Next step in the middleware chain.
     * @return \Illuminate\Http\JsonResponse|mixed Response from the next middleware or a 403 error.
     */
    public function handle($request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user && $user->company && $user->company->isExpired()) {
            return response()->json(['message' => 'Your company subscription has expired.'], 403);
        }

        return $next($request);
    }
}
