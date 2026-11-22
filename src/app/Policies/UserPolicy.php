<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'petugas'])
            && $user->can('view_any_user');
    }

    public function view(User $user, ?User $target = null): bool
    {
        return $user->hasAnyRole(['super_admin', 'petugas'])
            && $user->can('view_user');
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) && $user->can('create_user');
    }

    public function update(User $user, User $target): bool
    {
        return $this->isAdmin($user) && $user->can('update_user');
    }

    public function delete(User $user, User $target): bool
    {
        return $this->isAdmin($user)
            && $user->isNot($target)
            && $user->can('delete_user');
    }

    public function deleteAny(User $user): bool
    {
        // Lindungi admin aktif dan data akun dari bulk-delete tak sengaja.
        return false;
    }

    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $target): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, User $target): bool
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
