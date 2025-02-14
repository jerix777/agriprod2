<?php

namespace App\Policies;

use App\Models\User;

class ParcellePolicy
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
        return $user->can('view parcelle');
    }

    public function create(User $user): bool
    {
        return $user->can('create parcelle');
    }

    public function update(User $user): bool
    {
        return $user->can('edit parcelle');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete parcelle');
    }
}
