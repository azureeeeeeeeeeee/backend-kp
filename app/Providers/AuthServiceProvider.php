<?php

namespace App\Providers;

use App\Models\PPDBSetting;
use App\Models\Teacher;
use App\Policies\TeacherPolicy;
use App\Models\Admission;
use App\Policies\AdmissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Teacher::class => TeacherPolicy::class,
        Admission::class => AdmissionPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('update', function ($user) {
            // Cek apakah user adalah admin (sesuaikan dengan logic Anda)
            return $user && $user->role === 'admin'; // atau $user->is_admin
        });
    }
}