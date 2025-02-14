<?php

namespace App\Policies;

use App\Models\User;

class DepartementPolicy
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
        return $user->can('view departement');
    }

    public function create(User $user): bool
    {
        return $user->can('create departement');
    }

    public function update(User $user): bool
    {
        return $user->can('edit departement');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete departement');
    }
}
