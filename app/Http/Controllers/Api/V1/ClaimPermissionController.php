<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClaimPermissionRequest;
use App\Models\ClaimPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimPermissionController extends Controller
{
    public function index(Request $request)
    {
        $claimPermissionsQuery = ClaimPermission::with(
            'user.doctor.category',
            'user.doctor.employmentInformation',
            'user.staff.employmentInformation',
            'claimItem',
        );

        if ($request->has('status')) {
            $claimPermissionsQuery->where('status', $request->status);
        }

        if ($request->has('clinic_id')) {
            $claimPermissionsQuery->where('clinic_id', $request->clinic_id);
        }

        if ($request->has('user_id')) {
            $claimPermissionsQuery->where('user_id', $request->user_id);
        }

        $perPage = $request->input('per_page', 10);
        $claimPermissions = $claimPermissionsQuery->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission retrieved.',
            'data' => $claimPermissions,
        ]);
    }

    public function store(StoreClaimPermissionRequest $request)
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

        $claimPermission = new ClaimPermission();
        $claimPermission->user_id = $user->id;
        $claimPermission->clinic_id = $clinicID;
        $claimPermission->claim_item_id = $validated['claim_item_id'];
        $claimPermission->month = $validated['month'];
        $claimPermission->amount = $validated['amount'];

        $path = PermissionHelper::uploadAttachment($user->id, $request->file('attachment'), 'permission/claim');
        $claimPermission->attachment = $path;

        $claimPermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission created.',
            'data' => $claimPermission,
        ]);
    }

    // show
    public function show(int $id)
    {
        $claimPermission = ClaimPermission::with(
            'user.doctor.category',
            'user.doctor.employmentInformation',
            'user.staff.employmentInformation',
        )->find($id);
        if (!$claimPermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim permission not found.',
                'id' => $id
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission retrieved.',
            'data' => $claimPermission,
        ]);
    }

    // approve
    public function approve(int $id)
    {
        $claimPermission = ClaimPermission::find($id);
        if (!$claimPermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $claimPermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot approve your own claim permission.',
                'id' => $id
            ], 400);
        }

        $claimPermission->status = "approved";
        $claimPermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission approved.',
            'data' => $claimPermission,
        ]);
    }

    // decline
    public function decline(int $id)
    {
        $claimPermission = ClaimPermission::find($id);
        if (!$claimPermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim permission not found.',
                'id' => $id
            ], 404);
        }

        $userID = Auth::user()->id;
        if ($userID == $claimPermission->user_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You cannot decline your own claim permission.',
                'id' => $id
            ], 400);
        }

        $claimPermission->status = "declined";
        $claimPermission->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission declined.',
            'data' => $claimPermission,
        ]);
    }

    public function destroy(int $id)
    {
        $claimPermission = ClaimPermission::find($id);
        if (!$claimPermission) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim permission not found.',
                'id' => $id
            ], 404);
        }
        $claimPermission->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission deleted.',
        ]);
    }
}
