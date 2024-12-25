<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeavePermissionRequest;
use App\Models\LeaveBalance;
use App\Models\LeavePermission;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeavePermissionController extends Controller
{
    public function index(Request $request)
    {
        $leavePermissionQuery = LeavePermission::with(
            'user.doctor.category',
            'user.doctor.employmentInformation',
            'user.staff.employmentInformation',
            'leaveType',
        );

        if ($request->has('status')) {
            $leavePermissionQuery->where('status', $request->status);
        }

        if ($request->has('clinic_id')) {
            $leavePermissionQuery->where('clinic_id', $request->clinic_id);
        }

        if ($request->has('user_id')) {
            $leavePermissionQuery->where('user_id', $request->user_id);
        }

        $perPage = $request->input('per_page', 10);
        $leavePermission = $leavePermissionQuery->paginate($perPage);

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

        // validasi sisa saldo
        $leaveBalance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->first();

        if (!$leaveBalance) {
            DB::beginTransaction();
            try {
                $leaveType = LeaveType::all();

                foreach ($leaveType as $type) {
                    LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $type->id,
                        'bal' => $type->year_ent,
                    ]);
                }
                DB::commit();

                $leaveBalance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', $validated['leave_type_id'])
                    ->first();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => '[LeaveBalance] gagal menyimpan data',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // ambil jumlah hari cuti
        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);
        $requestedDays = $dateFrom->diffInDays($dateTo) + 1;

        if ($leaveBalance->bal < $requestedDays) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient leave balance.',
                'data' => [
                    'available_balance' => $leaveBalance->bal,
                    'requested_days' => $requestedDays,
                ],
            ], 400);
        }

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

        $leaveBalance = LeaveBalance::where('user_id', $leavePermission->user_id)
            ->where('leave_type_id', $leavePermission->leave_type_id)
            ->first();

        $dateFrom = Carbon::parse($leavePermission->date_from);
        $dateTo = Carbon::parse($leavePermission->date_to);
        $requestedDays = $dateFrom->diffInDays($dateTo) + 1;

        $leaveBalance->bal = $leaveBalance->bal - $requestedDays;
        $leaveBalance->taken = $leaveBalance->taken + $requestedDays;
        $leaveBalance->save();

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
