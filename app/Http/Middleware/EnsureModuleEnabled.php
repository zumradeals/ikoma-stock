<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        $user = $request->user();

        // SUPER_ADMIN n'a pas de société → pas de restriction module.
        if ($user === null || $user->company_id === null) {
            return $next($request);
        }

        if (! $user->company->hasModule($moduleCode)) {
            return redirect()->route('app.dashboard')
                ->with('error', 'Cette fonctionnalité n\'est pas activée pour votre société.');
        }

        return $next($request);
    }
}
