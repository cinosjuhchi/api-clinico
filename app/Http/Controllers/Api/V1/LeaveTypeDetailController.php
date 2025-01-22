<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LeaveTypeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveTypeDetailController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // jika admin
        if ($user->role == 'admin' || $user->role == 'superadmin') {
            $clinicID = null;
        } else {
            // jika clinic
            $clinic = match ($user->role) {
                'clinic' => $user->clinic,
                'doctor' => $user->doctor->clinic,
                'staff' => $user->staff->clinic,
                default => abort(401, 'Unauthorized access. Invalid role.'),
            };
            $clinicID = $clinic->id;
        }
        $leaveDetailsQuery = LeaveTypeDetail::with("leaveType","clinic");

        // get query param
        $leaveTypeID = $request->query("leave_type_id");

            $leaveDetailsQuery->where("clinic_id", $clinicID);
            $leaveDetails = $leaveDetailsQuery->get();

            if ($leaveDetails->isEmpty()) {
                // jika clinicid null
                if ($clinicID != null) {
                    // cek existance klinik id
                    $clinicById = Clinic::find($clinicID);
                    if (!$clinicById) {
                        return response()->json([
                            "status" => "failed",
                            "message" => "Clinic not found"
                        ], 400);
                    }
                }
                $this->createLeaveDetail($clinicID);
                $leaveDetailsQuery = LeaveTypeDetail::with("leaveType", "clinic")
                                                    ->where("clinic_id", $clinicID);
            }

        // filter by leave_type_id
        if ($leaveTypeID) {
            $leaveDetailsQuery->where("leave_type_id", $leaveTypeID);
        }

        $leaveDetails = $leaveDetailsQuery->get();
        return response()->json([
            "status" => "success",
            "message" => "Get leave details",
            "data" => $leaveDetails,
        ]);
    }

    public function createLeaveDetail($clinicID)
    {
        $leaveType = LeaveType::get();
        foreach ($leaveType as $leaveTypeItem) {
            LeaveTypeDetail::create([
                "clinic_id" => $clinicID,
                "leave_type_id" => $leaveTypeItem->id,
            ]);
        }
        Log::info(`success create leave_type_details with clinic_id: $clinicID`);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            "year_ent" => "required|numeric",
        ]);

        $leaveDetail = LeaveTypeDetail::find($id);

        if ($leaveDetail) {
            LeaveBalance::where("leave_type_detail_id", $leaveDetail->id)
                    ->where(function ($query) use ($request) {
                        $query->where("bal", 0)
                            ->orWhere("bal", ">", $request->year_ent);
                    })
                    ->update([
                        "bal" => $request->year_ent,
                    ]);

            $leaveDetail->update(["year_ent" => $request->year_ent]);
            return response()->json([
                "status" => "success",
                "message" => "Leave detail updated",
                "data" => $leaveDetail,
            ]);
        } else {
            return response()->json([
                "status" => "failed",
                "message" => "Leave detail not found",
            ], 404);
        }
    }
}
