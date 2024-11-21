<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicProfileController extends Controller
{
    public function clinicPatient(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $day = $request->input('day');
        $date = $request->input('date');
        $clinic->load([
            'rooms',
            'location',
            'schedule',
            'appointments' => function ($query) use ($date) {
                $query->whereDate('appointment_date', $date);
            },
            'pendingAppointments' => function ($query) use ($date) {
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'pending');
            },
            'consultationAppointments' => function ($query) use ($date) {
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'consultation');
            },
            'completedAppointments' => function ($query) use ($date) {
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'completed');
            },
            'services',
            'doctors' => function ($query) use ($day) {
                $query->whereHas('schedules', function ($q) use ($day) {
                    $q->where('day', $day);
                })->with('category');
            },
        ]);

        // Kembalikan resource klinik dengan tambahan status dan pesan
        return response()->json([
            'status' => 'success',
            'message' => 'Success to get clinic data.',
            'data' => new ClinicResource($clinic),
        ]);
    }

}
