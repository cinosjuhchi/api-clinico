<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LeaveTypeDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $validated = Validator::make($request->all(), [
           'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => $validated->errors(),
            ], 422);
        }

        $userID = $request->query('user_id');
        $user = User::with("doctor.clinic", "clinic", "staff.clinic")->find($userID);
        if (!$user) {
            return response()->json([
                "status" => "failed",
                "message" => "User not found",
                "id" => $userID
            ]);
        }

        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        $clinicID = $clinic->id;

        $leaveBalanceQuery = LeaveBalance::with(
            'user.doctor.employmentInformation',
            'user.doctor.category',
            'user.staff.employmentInformation',
            'leaveTypeDetail.leaveType',
        );

        if ($userID) {
            $leaveBalances = $leaveBalanceQuery->where('user_id', $userID)->get();
            if ($leaveBalances->isEmpty()) {
                DB::beginTransaction();
                try {
                    $leaveTypeDetailByClinicID = LeaveTypeDetail::where("clinic_id", $clinicID)->get();
                    if ($leaveTypeDetailByClinicID->isEmpty()) {
                        $leaveType = LeaveType::all();
                        foreach ($leaveType as $type) {
                            LeaveTypeDetail::create([
                                'leave_type_id' => $type->id,
                                'clinic_id' => $clinicID,
                            ]);
                        }
                    }

                    foreach ($leaveTypeDetailByClinicID as $leaveDetail) {
                        LeaveBalance::create([
                            'user_id' => $userID,
                            'bal' => $leaveDetail->year_ent,
                            'leave_type_detail_id' => $leaveDetail->id
                        ]);
                    }

                    DB::commit();

                    $leaveBalances = $leaveBalanceQuery->where('user_id', $userID)->get();

                    return response()->json([
                        'message' => 'Leave balances successfully created',
                        'data' => $leaveBalances,
                    ], 201);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => '[LeaveBalance] gagal menyimpan data',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            return response()->json([
                'message' => 'Leave balances found',
                'data' => $leaveBalances
            ]);
        }

        $leaveBalances = $leaveBalanceQuery->get();
        return response()->json([
            'message' => 'success',
            'data' => $leaveBalances
        ]);
    }
}
