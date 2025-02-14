<?php

namespace App\Policies;

use App\Models\User;

class PiecePolicy
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
        return $user->can('view piece');
    }

    public function create(User $user): bool
    {
        return $user->can('create piece');
    }

    public function update(User $user): bool
    {
        return $user->can('edit piece');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete piece');
    }
}
