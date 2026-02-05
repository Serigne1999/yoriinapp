<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoUserRestriction
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Vérifie si c'est l'utilisateur démo
        if ($user && $user->email === 'demo@yoriinapp.com') {
            // Exemple de restriction : interdire les requêtes POST, PUT, DELETE
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                return response()->json([
                    'message' => 'Action interdite en mode démo.'
                ], 403);
            }
        }

        return $next($request);
    }
}
