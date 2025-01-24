<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffScheduleRequest;
use App\Models\StaffSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        // get model
        $schedulesQuery = StaffSchedule::with('staff.employmentInformation');

        // filter by day
        $day = $request->query('day');
        if ($day) {
            $schedulesQuery->where('day', $day);
        }

        $schedules = $schedulesQuery->get();

        // return response
        return response()->json([
            'status' => 'success',
            'message' => 'Get staff schedules backoffice',
            'data' => $schedules,
        ]);
    }

    public function show($id)
    {
        $schedule = StaffSchedule::with('staff.employmentInformation')->find($id);
        if (!$schedule) {
            return response()->json([
               'status' => 'failed',
               'message' => 'Schedule not found.',
               'id' => $id,
            ], 404);
        }

        return response()->json([
           'status' => 'success',
           'message' => 'Get staff schedule backoffice',
            'data' => $schedule,
        ]);
    }

    public function store(StoreStaffScheduleRequest $request)
    {
        // hanya boleh superadmin
        $user = Auth::user();
        $role = $user->role;
        if ($role!= 'superadmin') {
            return response()->json([
               'status' => 'failed',
               'message' => 'Forbidden',
            ], 403);
        }
        // jangan ada staff_id dan day yang sama
        $isScheduleExist = StaffSchedule::where('admin_clinico_id', $request["admin_clinico_id"])
                                        ->where('day', $request["day"])
                                        ->first();
        if ($isScheduleExist) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Schedule already exist',
                'data' => $isScheduleExist,
            ], 400);
        }

        $schedule = new StaffSchedule();
        $schedule->admin_clinico_id = $request["admin_clinico_id"];
        $schedule->day = $request["day"];
        $schedule->start_work = $request["start_work"];
        $schedule->end_work = $request["end_work"];
        $schedule->save();

        return response()->json([
            'status' => 'success',
           'message' => 'Staff schedule created.',
            'data' => $schedule,
        ], 201);
    }

    public function update(StoreStaffScheduleRequest $request, $id)
    {
        // hanya boleh superadmin
        $user = Auth::user();
        $role = $user->role;
        if ($role!= 'superadmin') {
            return response()->json([
               'status' => 'failed',
               'message' => 'Forbidden',
            ], 403);
        }
        $schedule = StaffSchedule::find($id);
        if (!$schedule) {
            return response()->json([
               'status' => 'failed',
               'message' => 'Schedule not found.',
               'id' => $id,
            ], 404);
        }

        $schedule->admin_clinico_id = $request["admin_clinico_id"];
        $schedule->day = $request["day"];
        $schedule->start_work = $request["start_work"];
        $schedule->end_work = $request["end_work"];
        $schedule->save();

        return response()->json([
           'status' => 'success',
           'message' => 'Staff schedule updated.',
            'data' => $schedule,
        ], 200);
    }

    public function destroy($id)
    {
        $schedule = StaffSchedule::find($id);
        if (!$schedule) {
            return response()->json([
               'status' => 'failed',
               'message' => 'Schedule not found.',
               'id' => $id,
            ], 404);
        }

        $schedule->delete();

        return response()->json([
           'status' => 'success',
           'message' => 'Staff schedule deleted.',
            'id' => $id,
        ], 204);
    }
}
