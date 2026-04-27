<?php

namespace Shared\Middlewares;

use Closure;
use Installer\Repositories\Interfaces\InstallStateRepositoryInterface;

/**
 * Install wizard middleware - blocks re-entering the installer when the application is already installed.
 */
readonly class InstallGate
{
    /**
     * Injects the install state repository the middleware reads from to check whether the install has been completed.
     *
     * @param InstallStateRepositoryInterface $state Install state repository.
     */
    public function __construct(
        private InstallStateRepositoryInterface $state,
    ) {}

    /**
     * Handles the current request - returns 410 when the application is installed, otherwise passes the request through.
     *
     * @param mixed $request Current HTTP request.
     * @param Closure $next Next step in the middleware chain.
     * @return mixed Response from the next middleware or a 410 error.
     */
    public function handle($request, Closure $next): mixed
    {
        if ($this->state->isInstalled()) {
            return response()->json([
                'message' => 'Application is already installed.',
            ], 410);
        }

        return $next($request);
    }
}
