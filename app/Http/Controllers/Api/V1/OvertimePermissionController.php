<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOvertimePermissionRequest;
use App\Models\OvertimePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimePermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userID = $user->id;

        $overtimePermissionsQuery = OvertimePermission::with(
                                                            'user.doctor.category',
                                                            'user.doctor.employmentInformation',
                                                            'user.staff.employmentInformation',
                                                        )->where('user_id', $userID);
        if ($request->has('status')) {
            $overtimePermissionsQuery->where('status', $request->status);
        }

        if ($request->has('clinic_id')) {
            $overtimePermissionsQuery->where('clinic_id', $request->clinic_id);
        }

        $overtimePermissions = $overtimePermissionsQuery->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission retrieved.',
            'data' => $overtimePermissions,
        ]);
    }

    public function store(StoreOvertimePermissionRequest $request)
    {
        $user = Auth::user();
        $userID = $user->id;
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        $clinicID = $clinic->id;

        $validated = $request->validated();

        $overtimePermission = new OvertimePermission();
        $overtimePermission->user_id = $userID;
        $overtimePermission->date = $validated['date'];
        $overtimePermission->start_time = $validated['start_time'];
        $overtimePermission->end_time = $validated['end_time'];
        $overtimePermission->reason = $validated['reason'];
        $overtimePermission->clinic_id = $clinicID;
        $overtimePermission->status = "pending";

        $path = PermissionHelper::uploadAttachment($userID, $request->file('attachment'), 'permission/overtime');
        $overtimePermission->attachment = $path;

        $overtimePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission created.',
            'data' => $overtimePermission,
        ]);
    }

    public function show(int $id)
    {
        $overtimePermission = OvertimePermission::with(
                                                    'user.doctor.category',
                                                    'user.doctor.employmentInformation'
                                                )->find($id);
        if (!$overtimePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Overtime permission not found.',
                'id' => $id
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission retrieved.',
            'data' => $overtimePermission,
        ]);
    }

    public function approve(int $id)
    {
        $overtimePermission = OvertimePermission::find($id);
        if (!$overtimePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Overtime permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $overtimePermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot approve your own overtime permission.',
                'id' => $id
            ], 400);
        }

        $overtimePermission->status = "approved";
        $overtimePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission approved.',
            'data' => $overtimePermission,
        ]);
    }

    public function decline(int $id)
    {
        $overtimePermission = OvertimePermission::find($id);
        if (!$overtimePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Overtime permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $overtimePermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot decline your own overtime permission.',
                'id' => $id
            ], 400);
        }

        $overtimePermission->status = "declined";
        $overtimePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission declined.',
            'data' => $overtimePermission,
        ]);
    }

    public function destroy(int $id)
    {
        $overtimePermission = OvertimePermission::find($id);
        if (!$overtimePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Overtime permission not found.',
                'id' => $id
            ], 404);
        }
        $overtimePermission->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission deleted.',
        ]);
    }
}
