<?php

namespace App\Policies;

use App\Models\User;

class CulturePolicy
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
        return $user->can('view culture');
    }

    public function create(User $user): bool
    {
        return $user->can('create culture');
    }

    public function update(User $user): bool
    {
        return $user->can('edit culture');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete culture');
    }
}
