<?php

namespace App\Http\Middleware;

use App\Traits\BelongsToTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Défense en profondeur en plus du CompanyScope global : si une route reçoit
 * un modèle métier lié (route model binding), vérifie explicitement qu'il
 * appartient à la société de l'utilisateur connecté, même si le scope a été
 * contourné (withoutGlobalScope, requête brute, etc.).
 */
class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        // SUPER_ADMIN (company_id null) : aucune restriction tenant.
        if ($user->company_id === null) {
            return $next($request);
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (! is_object($parameter)) {
                continue;
            }

            if (! in_array(BelongsToTenant::class, class_uses_recursive($parameter), true)) {
                continue;
            }

            abort_if($parameter->company_id !== $user->company_id, 403);
        }

        return $next($request);
    }
}
