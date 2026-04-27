<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware that sets the team context (company id) for the Spatie permissions system — teams mode.
 */
class SetPermissionsTeamId
{
    /**
     * Sets the authenticated user's company id as the current Spatie team.
     *
     * @param Request $request Current HTTP request.
     * @param Closure $next Next middleware in the chain.
     * @return mixed Response from the rest of the pipeline.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            setPermissionsTeamId($request->user()->company_id);
        }

        return $next($request);
    }
}
