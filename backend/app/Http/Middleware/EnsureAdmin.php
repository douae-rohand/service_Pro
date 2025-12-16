<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié',
                'error' => 'Vous devez être connecté pour accéder à cette ressource.'
            ], 401);
        }

        // Vérifier que l'utilisateur est un admin
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Accès refusé',
                'error' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.'
            ], 403);
        }

        return $next($request);
    }
}

