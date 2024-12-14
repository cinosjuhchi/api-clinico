<?php

namespace App\Helpers;

use App\Models\Attendance;
use App\Models\DoctorSchedule;

class AttendanceHelper
{
    public static function isDistanceGreaterThan1Km($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // 6.371.000 meter

        // konversi derajat ke radian
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // rumus -> https://www.movable-type.co.uk/scripts/latlong.html
        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // dalam meter

        return $distance > 20; // lebih dari 20 meter
    }

    public static function getDoctorSchedule($doctorId)
    {
        $dayToday = now()->format('l');
        $userSchedule = DoctorSchedule::where('doctor_id', $doctorId)
                                    ->where('day', $dayToday)
                                    ->first();
        if (!$userSchedule) {
            return response()->json([
                "status" => "error",
                "message" => "You have no schedule today",
            ], 400);
        }

        return $userSchedule;
    }


    public static function getAttendanceByClockIn($userId)
    {
        $attendance = Attendance::where('user_id', $userId)
                                ->whereDate('clock_in', now()->toDateString())
                                ->first();
        return $attendance;
    }
}
