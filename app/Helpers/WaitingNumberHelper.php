<?php

namespace App\Helpers;

use App\Models\Appointment;

class WaitingNumberHelper
{
    public static function generate($date, $doctor_id, $room_id)
    {
        // Check appointment with consultation status first
        $bookedConsultation = Appointment::where('appointment_date', $date)
            ->where('status', 'consultation')
            ->where('doctor_id', $doctor_id)
            ->where('room_id', $room_id)
            ->latest('updated_at')
            ->first();

        // If no consultation status, check on-consultation status
        $bookedOnConsultation = null;
        if (!$bookedConsultation) {
            $bookedOnConsultation = Appointment::where('appointment_date', $date)
                ->where('status', 'on-consultation')
                ->where('doctor_id', $doctor_id)
                ->where('room_id', $room_id)
                ->latest('updated_at')
                ->first();
        }

        // Determine waiting number based on check results
        $waitingNumber = 1;
        if ($bookedConsultation) {
            $waitingNumber = $bookedConsultation->waiting_number + 1;
        } elseif ($bookedOnConsultation) {
            $waitingNumber = $bookedOnConsultation->waiting_number + 1;
        }

        return $waitingNumber;
    }
}
