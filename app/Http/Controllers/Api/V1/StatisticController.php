<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    public function consultationCompleted(Request $request)
    {
        // get clinic
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        // get todays
        $today = Carbon::now();

        // get last 7 days
        $last7Days = $today->copy()->subDays(7);

        // get by clinic_id
        $appointmentsQuery = $clinic->appointments()
                                ->where('status', 'completed')
                                ->whereBetween('appointment_date', [$last7Days, $today])
                                ->orderBy('appointment_date', 'asc');

        // filter by doctor_id
        $doctorID = $request->input('doctor_id');
        if ($doctorID) {
            $appointmentsQuery->where('doctor_id', $doctorID);
        }

        $appointments = $appointmentsQuery->get();

        // Group appointments by date
        $groupedAppointments = $appointments->groupBy(function ($appointment) {
            return Carbon::parse($appointment->appointment_date)->toDateString();
        });

        // Create a range of dates for the last 7 days
        $allDates = collect();
        for ($i = 1; $i <= 7; $i++) {
            $date = $last7Days->copy()->addDays($i);
            $allDates->put($date->toDateString(), $date->format('l')); // Store both date and weekday name
        }

        // Create the daily totals, setting 0 for days without appointments
        $dailyTotals = $allDates->map(function ($weekday, $date) use ($groupedAppointments) {
            return [
                'date' => $date,
                'weekday' => $weekday,
                'appointments' => $groupedAppointments->has($date) ? $groupedAppointments[$date]->count() : 0
            ];
        });

        return response()->json([
            'message' => 'Get completed appointments by last 7 days successfully',
            'data' => [
                'daily-totals' => $dailyTotals,
                'appointments' => $appointments,
            ],
        ]);
    }
}
