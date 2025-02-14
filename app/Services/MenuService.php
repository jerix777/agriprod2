<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class MenuService
{
    public function canDisplayMenuItem($permittedRoles)
    {

        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();

        // Vérifier si l'utilisateur actuel a la permission requise
        return Auth::user() && in_array($roleName, $permittedRoles);
    }

    public function getRouteForItem($item)
    {
        $role = strtolower(Auth::user()->getRoleNames()->first());

        return route($role.'s.'.$item['route']);
    }
}
