<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OnlineEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $doctorUsers = $clinic->doctorUsers()
            ->where('role', 'doctor')
            ->with([
                'doctor',
                'doctor.employmentInformation',
            ])
            ->get();

        // Filter only active users
        $activeDoctorUsers = $doctorUsers->filter(function ($doctor) {
            $lastSeen = $doctor->last_seen
            ? Carbon::parse($doctor->last_seen)
            : null;

            return $lastSeen && $lastSeen->gt(Carbon::now()->subMinutes(5));
        });

        // Map data to the required format
        $activeDoctorUsers = $activeDoctorUsers->map(function ($doctor) {
            return [
                'doctor_id' => $doctor->doctor->id,
                'doctor_name' => $doctor->doctor->name,
                'image' => $doctor->doctor->employmentInformation->image_profile,
                'email' => $doctor->email,
                'phone_number' => $doctor->phone_number,
                'last_seen' => $doctor->last_seen,
                'active' => 'active',
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch active doctor users.',
            'data' => $activeDoctorUsers,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
