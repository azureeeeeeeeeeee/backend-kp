<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user->role === 'admin'; // semua boleh lihat daftar data
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Admission $admission): bool
    {
        return true; // semua boleh lihat detail pendaftaran
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): Response
    {
        // semua boleh daftar (bahkan tanpa login)
        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Admission $admission): Response
    {
        // hanya admin yang boleh verifikasi/update data
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Hanya admin yang bisa mengubah data pendaftaran');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Admission $admission): Response
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('Hanya admin yang bisa menghapus data pendaftaran');
    }

    public function restore(User $user, Admission $admission): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Admission $admission): bool
    {
        return $user->role === 'admin';
    }
}
