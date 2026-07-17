<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tant que storage/installed n'existe pas (voir InstallController::finish()),
 * toute page redirige vers l'assistant d'installation ; une fois installé,
 * l'inverse (l'assistant devient inaccessible, pour ne pas pouvoir relancer
 * une installation par-dessus une instance déjà en service).
 */
class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('installed'));

        if (! $installed && ! $request->is('install*')) {
            return redirect('/install');
        }

        if ($installed && $request->is('install*')) {
            abort(403, "L'application est déjà installée.");
        }

        return $next($request);
    }
}
