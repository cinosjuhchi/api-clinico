<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class AppointmentObserver
{
    public function saving(Appointment $appointment)
    {
        $today = Carbon::now()->toDateString();
        Log::info('Today: ' . $today);
        Log::info('Appointment Date: ' . $appointment->appointment_date);

        if ($appointment->appointment_date < $today && $appointment->status == 'pending') {
            $appointment->status = 'cancelled';
        }
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
