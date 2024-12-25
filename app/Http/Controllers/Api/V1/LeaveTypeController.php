<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::all();

        return response()->json([
            'message' => 'get leave types.',
            'data' => $leaveTypes
        ]);
    }

    public function show(int $id)
    {
        $leaveType = LeaveType::find($id);
        if (!$leaveType) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave type not found.',
                'id' => $id
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Get leave type.',
            'data' => $leaveType,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'description' => 'required',
            'year_ent' => 'required|numeric',
        ]);

        $leaveType = LeaveType::create([
            'code' => $request->code,
            'description' => $request->description,
            'year_ent' => $request->year_ent,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type created.',
            'data' => $leaveType,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'year_ent' => 'required|numeric',
        ]);

        $leaveType = LeaveType::find($id);
        if (!$leaveType) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave type not found.',
                'id' => $id
            ], 404);
        }

        $leaveType->update(['year_ent' => $request->year_ent]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type updated.',
            'data' => $leaveType,
        ]);
    }

    public function destroy(int $id)
    {
        $leaveType = LeaveType::find($id);
        if (!$leaveType) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Leave type not found.',
                'id' => $id
            ], 404);
        }
        $leaveType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave type deleted.',
        ]);
    }
}
