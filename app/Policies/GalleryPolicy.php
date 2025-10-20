<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GalleryPolicy
{
    /**
     * Tentukan apakah user dapat membuat galeri.
     */
    public function create(User $user)
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Admin only');
    }

    /**
     * Tentukan apakah user dapat memperbarui galeri.
     */
    public function update(User $user)
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Admin only');
    }

    public function delete(User $user)
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Admin only');
    }


    /**
     * Opsi lainnya (tidak digunakan tapi disiapkan biar Laravel tidak error)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return true;
    }

    public function restore(User $user, Gallery $gallery): bool
    {
        return false;
    }

    public function forceDelete(User $user, Gallery $gallery): bool
    {
        return false;
    }
}
