<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeavePermissionRequest;
use App\Models\LeavePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeavePermissionController extends Controller
{
    public function index(Request $request)
    {
        $leavePermissionQuery = LeavePermission::with('user', 'leaveType');

        if ($request->has('status')) {
            $leavePermissionQuery->where('status', $request->status);
        }

        if ($request->has('clinic_id')) {
            $leavePermissionQuery->where('clinic_id', $request->clinic_id);
        }

        if ($request->has('user_id')) {
            $leavePermissionQuery->where('user_id', $request->user_id);
        }

        $leavePermission = $leavePermissionQuery->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission retrieved.',
            'data' => $leavePermission,
        ]);
    }

    public function store(StoreLeavePermissionRequest $request)
    {
        $user = Auth::user();

        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        $clinicID = $clinic->id;

        $validated = $request->validated();

        $leavePermission = new LeavePermission();
        $leavePermission->date_from = $validated['date_from'];
        $leavePermission->date_to = $validated['date_to'];
        $leavePermission->reason = $validated['reason'];
        $leavePermission->leave_type_id = $validated['leave_type_id'];
        $leavePermission->user_id = $user->id;
        $leavePermission->clinic_id = $clinicID;

        $path = PermissionHelper::uploadAttachment($user->id, $request->file('attachment'), 'permission/leave');
        $leavePermission->attachment = $path;

        $leavePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission created.',
            'data' => $leavePermission,
        ]);
    }

    public function show(int $id)
    {
        $leavePermission = LeavePermission::with('user', 'leaveType')->find($id);
        if (!$leavePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave permission not found.',
                'id' => $id
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission retrieved.',
            'data' => $leavePermission,
        ]);
    }

    public function destroy(int $id)
    {
        $leavePermission = LeavePermission::find($id);
        if (!$leavePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave permission not found.',
                'id' => $id
            ], 404);
        }
        $leavePermission->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission deleted.',
        ]);
    }

    public function approve(int $id)
    {
        $leavePermission = LeavePermission::find($id);
        if (!$leavePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $leavePermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot approve your own leave permission.',
                'id' => $id
            ], 400);
        }

        $leavePermission->status = "approved";
        $leavePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission approved.',
            'data' => $leavePermission,
        ]);
    }

    public function decline(int $id)
    {
        $leavePermission = LeavePermission::find($id);
        if (!$leavePermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $leavePermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot decline your own leave permission.',
                'id' => $id
            ], 400);
        }

        $leavePermission->status = "declined";
        $leavePermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission declined.',
            'data' => $leavePermission,
        ]);
    }
}
