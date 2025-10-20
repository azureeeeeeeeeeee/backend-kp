<?php

namespace App\Policies;

use App\Models\PPDBSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PPDBSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PPDBSetting $pPDBSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        // Sesuaikan dengan role kamu (admin, superadmin, dll)
        return $user->role === 'admin' || $user->role === 'superadmin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PPDBSetting $pPDBSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PPDBSetting $pPDBSetting): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PPDBSetting $pPDBSetting): bool
    {
        return false;
    }
}
