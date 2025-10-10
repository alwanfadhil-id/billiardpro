<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can manage tables.
     */
    public function manageTables(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage products.
     */
    public function manageProducts(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage reports.
     */
    public function manageReports(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }
}