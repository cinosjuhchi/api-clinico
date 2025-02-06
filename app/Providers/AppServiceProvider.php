<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\User;
use App\Observers\AppointmentObserver;
use App\Observers\UserObserver;
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
        Appointment::observe(AppointmentObserver::class);
        User::observe(UserObserver::class);
    }
}
