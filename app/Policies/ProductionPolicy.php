<?php

namespace App\Policies;

use App\Models\User;

class ProductionPolicy
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
        return $user->can('view production');
    }

    public function create(User $user): bool
    {
        return $user->can('create production');
    }

    public function update(User $user): bool
    {
        return $user->can('edit production');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete production');
    }
}
