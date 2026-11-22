<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) && $user->can('view_any_role');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->isAdmin($user) && $user->can('view_role');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) && $user->can('create_role');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->isAdmin($user)
            && $role->name !== 'super_admin'
            && $user->can('update_role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->isAdmin($user)
            && $role->name !== 'super_admin'
            && $user->can('delete_role');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, Role $role): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
