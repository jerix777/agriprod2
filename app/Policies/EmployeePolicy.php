<?php

namespace App\Policies;

use App\Models\User;

class EmployeePolicy
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
        return $user->can('view Employee');
    }

    public function create(User $user): bool
    {
        return $user->can('create Employee');
    }

    public function update(User $user): bool
    {
        return $user->can('edit Employee');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete Employee');
    }
}
