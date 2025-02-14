<?php

namespace App\Policies;

use App\Models\User;

class DemandePolicy
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
        return $user->can('view demande');
    }

    public function create(User $user): bool
    {
        return $user->can('create demande');
    }

    public function update(User $user): bool
    {
        return $user->can('edit demande');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete demande');
    }
}
