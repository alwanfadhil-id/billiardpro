<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for role-based access
        Gate::define('manage-tables', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('manage-products', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('manage-reports', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });
    }
}