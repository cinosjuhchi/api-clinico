<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorScheduleController extends Controller
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
            default => throw new \Exception('Unauthorized access. Invalid role.'),
        };

        try {
            $schedules = $clinic->doctorSchedule()->with(['doctor', 'room'])->paginate(10);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully retrieved',
                'data' => $schedules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve doctor schedules',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorScheduleRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            //code...
            DoctorSchedule::create($validated);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored data',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorSchedule $doctorSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DoctorSchedule $doctorSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule)
    {
        $validated = $request->validated();
        $doctorSchedule->fill($validated);
        if ($doctorSchedule->isDirty()) {
            DB::beginTransaction();
            try {
                $doctorSchedule->update($validated);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Success update data',
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed update the data.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorSchedule $doctorSchedule)
    {
        DB::beginTransaction();
        try {
            $doctorSchedule->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Succcess delete the data.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail update the data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
