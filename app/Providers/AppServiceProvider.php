<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DashboardPolicy;
use App\Dashboard;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Models\Category;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::define('update_appointment_status', function (User $user) {
        //     return $user->usertype === 'admin' && $user->hasRole('doctor') || $user->hasRole('super_admin');
        // });
    
        Gate::policy(Dashboard::class,DashboardPolicy::class);
       
    Gate::policy(Category::class, CategoryPolicy::class);
    }
    
    
}
