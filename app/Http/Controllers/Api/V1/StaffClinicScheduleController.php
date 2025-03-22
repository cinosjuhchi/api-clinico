<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\StoreStaffClinicScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Http\Requests\UpdateStaffClinicScheduleRequest;
use App\Models\StaffClinicSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffClinicScheduleController extends Controller
{
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
            $schedulesQuery = $clinic->staffClinicSchedule();

            $query = $request->input('q');

            $schedulesQuery->when($query, function ($q, $query) {
                $q->whereHas('staff', function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%");
                })
                ->orWhere('day', 'like', "%{$query}%");
            });

            // ?name=xx&day=xx
            if ($request->filled('name')) {
                $name = $request->name;
                $schedulesQuery->where(function ($query) use ($name) {
                    $query->whereHas('staff', function ($subQuery) use ($name) {
                        $subQuery->where('name', 'like', '%' . $name . '%');
                    });
                });
            }

            if ($request->filled('day')) {
                $schedulesQuery->where('day', $request->day);
            }

            $schedulesQuery->with('staff.employmentInformation');
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
                'message' => 'Failed to retrieve staff schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreStaffClinicScheduleRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            StaffClinicSchedule::create($validated);
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


    public function update(UpdateStaffClinicScheduleRequest $request, StaffClinicSchedule $staffClinicSchedule)
    {
        $validated = $request->validated();
        $staffClinicSchedule->fill($validated);
        if ($staffClinicSchedule->isDirty()) {
            DB::beginTransaction();
            try {
                $staffClinicSchedule->update($validated);
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $staffClinicSchedule = StaffClinicSchedule::findOrFail($id);
            $staffClinicSchedule->delete();
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
