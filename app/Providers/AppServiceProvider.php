<?php

namespace App\Providers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('add-completed-date', function (User $user, Assessment $assessment) {
            return $assessment->staff_id === $user->id;
        });

        Gate::define('view-student', function (User $user) {
            return $user->is_admin || $user->is_staff;
        });

        Gate::define('is-admin', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('add-complaint', function (User $user, Assessment $assessment) {
            return $assessment->course->students->contains($user->id);
        });
    }
}
