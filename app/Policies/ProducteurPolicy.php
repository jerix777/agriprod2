<?php

namespace App\Policies;

use App\Models\User;

class ProducteurPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user): bool
    {
        return $user->can('view producteur');
    }

    public function create(User $user): bool
    {
        return $user->can('create producteur');
    }

    public function update(User $user): bool
    {
        return $user->can('edit producteur');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete producteur');
    }
}
