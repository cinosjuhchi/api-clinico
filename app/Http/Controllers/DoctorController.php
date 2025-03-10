<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Doctor $doctor, Request $request)
{
    $day = $request->input('day');

    $doctor->load([
        'category',
        'clinic',
        'doctorSchedules' => function ($query) use ($day) {
            if ($day) {
                $query->where('day', $day)->first();
            }
        }
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Doctor retrieved successfully',
        'data' => $doctor
    ]);
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }
}
