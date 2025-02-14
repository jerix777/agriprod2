<?php

namespace App\Policies;

use App\Models\User;

class PostePolicy
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
        return $user->can('view poste');
    }

    public function create(User $user): bool
    {
        return $user->can('create poste');
    }

    public function update(User $user): bool
    {
        return $user->can('edit poste');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete poste');
    }
}
