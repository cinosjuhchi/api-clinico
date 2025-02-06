<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OnlineEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
                'user_id' => $doctor->id,
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

    public function onlineAdmin()
    {
        // if (Auth::user()->role != 'superadmin') {
        //     return response()->json([
        //        'status' => 'error',
        //        'message' => 'forbidden',
        //     ], 403);
        // }
        $admins = User::where('role', 'admin')
            ->with([
                'adminClinico',
                'adminClinico.employmentInformation',
            ])
            ->get();

        $activeAdmins = $admins->filter(function ($admin) {
            $lastSeen = $admin->last_seen
                ? Carbon::parse($admin->last_seen)
                : null;

            return $lastSeen && $lastSeen->gt(Carbon::now()->subMinutes(5));
        });

        $activeAdmins = $activeAdmins->map(function ($admin) {
            return [
                'user_id' => $admin->id,
                'admin_id' => $admin->adminClinico->id,
                'admin_name' => $admin->adminClinico->name,
                'image' => $admin->adminClinico->employmentInformation->image_profile,
                'email' => $admin->email,
                'phone_number' => $admin->phone_number,
                'last_seen' => $admin->last_seen,
                'active' => 'active',
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch all active admin',
            'data' => $activeAdmins,
        ], 200);
    }
}
