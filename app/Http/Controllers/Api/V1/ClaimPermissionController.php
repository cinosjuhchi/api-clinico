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
        $claimPermissionsQuery = ClaimPermission::with([
            'user',
            'claimItem',
        ]);

        $user = Auth::user();
        $role = $user->role;

        $relations = match ($role) {
            'doctor', 'clinic', 'staff' => ['user.doctor.category', 'user.doctor.employmentInformation', 'user.staff.employmentInformation'],
            'admin', 'superadmin' => ['user.adminClinico.employmentInformation'],
            default => abort(401, 'Unauthorized access. Invalid role.')
        };

        $claimPermissionsQuery->with($relations);

        if ($request->has('status')) {
            $statuses = $request->status;

            if (is_array($statuses)) {
                $claimPermissionsQuery->whereIn('status', $statuses);
            } else {
                $claimPermissionsQuery->where('status', $statuses);
            }
        }

        if ($request->has('clinic_id')) {
            $claimPermissionsQuery->when($request->clinic_id == 0, function ($query) {
                return $query->whereNull('clinic_id');
            }, function ($query) use ($request) {
                return $query->where('clinic_id', $request->clinic_id);
            });
        }

        if ($request->has('user_id')) {
            $claimPermissionsQuery->where('user_id', $request->user_id);
        }

        if ($request->has(['start_date', 'end_date'])) {
            $claimPermissionsQuery->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $paginate = true;
        if ($request->has('paginate')) {
            $paginate = $request->input('paginate');
        }

        $perPage = $request->input('per_page', 10);

        if ($paginate) {
            $claimPermission = $claimPermissionsQuery->paginate($perPage);
        } else {
            $claimPermission = $claimPermissionsQuery->get();
        }

        if ($request->has('group_by') && $request->input('group_by') == 'date') {
            $groupedData = $claimPermission->groupBy('created_at');

            $formattedData = $groupedData->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'total_requests' => $items->count(),
                    'total_amount' => $items->sum('amount'),
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'user_id' => $item->user_id,
                            'clinic_id' => $item->clinic_id,
                            'claim_item_id' => $item->claim_item_id,
                            'month' => $item->month,
                            'amount' => $item->amount,
                            'attachment' => $item->attachment,
                            'status' => $item->status,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'user' => $item->user,
                            'claim_item' => $item->claimItem,
                        ];
                    }),
                ];
            });

            if ($paginate) {
                $claimPermission->setCollection($formattedData->values());
            } else {
                $claimPermission = $formattedData->values();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Claim permission retrieved.',
            'data' => $claimPermission,
        ]);
    }

    public function store(StoreClaimPermissionRequest $request)
    {
        $user = Auth::user();

        // jika admin
        if ($user->role == 'admin') {
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
