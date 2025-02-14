<?php

// App\Http\Middleware\RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        if (! Auth::check() || ! Auth::user()->hasRole($role)) {
            // Rediriger ou afficher une erreur si l'utilisateur n'a pas le rôle requis
            return redirect('/')->with('error', 'Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        return $next($request);
    }
}
