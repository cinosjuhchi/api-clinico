<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CancelExpiredAppointments extends Command
{
    protected $signature = 'appointments:cancel-expired';
    protected $description = 'Cancel pending appointments that have passed their scheduled date and time';

    public function handle()
    {        
        $today = Carbon::now()->toDateString();

        $expiredAppointments = Appointment::where(function ($query) use ($today) {
            $query->where('appointment_date', '<', $today)
                  ->where(function ($query) {
                      $query->where('status', 'pending')
                            ->orWhere('status', 'consultation');
                  });
        })->get();
        foreach ($expiredAppointments as $appointment) {
            $this->info($today);
            $this->info($appointment->appointment_date);
            $appointment->update(['status' => 'cancelled']);
            $this->info("Appointment ID {$appointment->id} has been cancelled.");
        }
        $this->info("Total pending appointments cancelled: " . $expiredAppointments->count() . ' at : ' . $today);
    }
}
