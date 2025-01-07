<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\LeaveType;
use App\Models\LeaveTypeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveTypeDetailController extends Controller
{
    public function index(Request $request)
    {
        $leaveDetailsQuery = LeaveTypeDetail::with("leaveType","clinic",);

        // get query param
        $clinicID = $request->query("clinic_id");
        $leaveTypeID = $request->query("leave_type_id");

        // filter by clinic_id
        if ($clinicID) {
            $leaveDetailsQuery->where("clinic_id", $clinicID);
            $leaveDetails = $leaveDetailsQuery->get();

            if ($leaveDetails->isEmpty()) {
                // cek existance klinik id
                $clinicById = Clinic::find($clinicID);
                if (!$clinicById) {
                    return response()->json([
                        "status" => "failed",
                        "message" => "Clinic not found"
                    ], 400);
                }
                $this->createLeaveDetail($clinicID);
                $leaveDetailsQuery = LeaveTypeDetail::with("leaveType", "clinic")
                                                    ->where("clinic_id", $clinicID);
            }
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

    public function createLeaveDetail(int $clinicID)
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
