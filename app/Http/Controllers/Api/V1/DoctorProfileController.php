<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $doctor = Doctor::with([
            'clinic',
            'category',
            'schedules',
            'doctorSchedules',
            'financialInformation',
            'parentInformation',
            'childsInformation',
            'spouseInformation',
            'emergencyContact',
            'contributionInfo',
            'basicSkills',
            'employmentInformation',
            'reference',
            'educational',
            'demographic',
            'user'
        ])
            ->where('user_id', $id)
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch doctor profile is successfully!',
            'data' => new DoctorResource($doctor),
        ]);
    }

    public function doctorPatient(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $day = $request->input('day');
        $date = $request->input('date');

        $doctor = Doctor::with([
            'clinic',
            'category',
            'schedules',
            'appointments' => function ($query) use ($date) {
                // Ambil semua appointment pada hari ini
                $query->whereDate('appointment_date', $date);
            },
            'pendingAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'pending');
            },
            'consultationAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'consultation');
            },
            'completedAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'completed');
            },
        ])
            ->where('user_id', $id)
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch doctor profile is successfully!',
            'data' => new DoctorResource($doctor),
        ]);

    }
}
