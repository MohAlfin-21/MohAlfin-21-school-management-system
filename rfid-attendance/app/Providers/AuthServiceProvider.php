<?php

namespace App\Providers;

use App\Models\AbsenceRequest;
use App\Models\StudentProfile;
use App\Policies\AbsenceRequestPolicy;
use App\Policies\StudentProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        StudentProfile::class => StudentProfilePolicy::class,
        AbsenceRequest::class => AbsenceRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}

