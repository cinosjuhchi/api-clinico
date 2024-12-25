<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
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

        $leaveBalanceQuery = LeaveBalance::with(
            'user.doctor.employmentInformation',
            'user.doctor.category',
            'user.staff.employmentInformation',
            'leaveType',
        );

        $userID = $request->query('user_id');
        if ($userID) {
            $leaveBalances = $leaveBalanceQuery->where('user_id', $userID)->get();
            if (count($leaveBalances) == 0) {
                DB::beginTransaction();
                try {
                    $leaveType = LeaveType::all();

                    foreach ($leaveType as $type) {
                        LeaveBalance::create([
                            'user_id' => $userID,
                            'leave_type_id' => $type->id,
                            'bal' => $type->year_ent,
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
