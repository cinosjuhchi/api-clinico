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
        $expiredAppointments = Appointment::where('appointment_date', '<', $today)
                                          ->where('status', 'pending')                                          
                                          ->get();
        foreach ($expiredAppointments as $appointment) {
            $this->info($today);
            $appointment->update(['status' => 'cancelled']);
            $this->info("Appointment ID {$appointment->id} has been cancelled.");
        }
        $this->info("Total pending appointments cancelled: " . $expiredAppointments->count());
    }
}
