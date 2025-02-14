<?php

namespace App\Policies;

use App\Models\User;

class GenrePolicy
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
        return $user->can('view genre');
    }

    public function create(User $user): bool
    {
        return $user->can('create genre');
    }

    public function update(User $user): bool
    {
        return $user->can('edit genre');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete genre');
    }
}
