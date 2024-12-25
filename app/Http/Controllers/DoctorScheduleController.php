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
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        try {
            $schedulesQuery = $clinic->doctorSchedule();

            // ?name=xx&day=xx&room=xx
            if ($request->filled('name')) {
                $name = $request->name;
                $schedulesQuery->where(function ($query) use ($name) {
                    $query->whereHas('doctor', function ($subQuery) use ($name) {
                        $subQuery->where('name', 'like', '%' . $name . '%');
                    });
                });
            }

            if ($request->filled('day')) {
                $schedulesQuery->where('day', $request->day);
            }

            if ($request->filled('room')) {
                $room = $request->room;
                $schedulesQuery->whereHas('room', function ($query) use ($room) {
                    $query->where('name', 'like', '%' . $room . '%');
                });
            }

            $schedulesQuery->with(['doctor.category', 'room']);
            $paginate = filter_var($request->input('paginate', 'true'), FILTER_VALIDATE_BOOLEAN);
            $schedules = $paginate
                ? $schedulesQuery->paginate($request->input('per_page', 10))
                : $schedulesQuery->get();

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
