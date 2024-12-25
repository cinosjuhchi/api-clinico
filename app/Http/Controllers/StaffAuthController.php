<?php

namespace App\Http\Controllers;

use App\Http\Resources\DoctorResource;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class StaffAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if ($user->role != 'staff') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Role Access',
            ], 401);
        }
        $staff = $user->staff;
        $staff->with([
            'staff',
            'clinic',
            'demographic',
            'educational',
            'contributionInfo',
            'emergencyContact',
            'spouseInformation',
            'childsInformation',
            'parentInformation',
            'reference',
            'basicSkills',
            'financialInformation',
            'employmentInformation'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch doctor profile is successfully!',
            'data' => new DoctorResource($doctor),
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Logout"], 200);
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
    public function show(Staff $staff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        //
    }
}
