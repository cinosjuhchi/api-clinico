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
        $overtimePermissionsQuery = OvertimePermission::with([
            'user',
        ]);

        $user = Auth::user();
        $role = $user->role;

        $relations = match ($role) {
            'doctor', 'clinic', 'staff' => ['user.doctor.category', 'user.doctor.employmentInformation', 'user.staff.employmentInformation'],
            'admin', 'superadmin' => ['user.adminClinico.employmentInformation'],
            default => abort(401, 'Unauthorized access. Invalid role.')
        };

        $overtimePermissionsQuery->with($relations);

        if ($request->has('status')) {
            $statuses = $request->status;

            if (is_array($statuses)) {
                $overtimePermissionsQuery->whereIn('status', $statuses);
            } else {
                $overtimePermissionsQuery->where('status', $statuses);
            }
        }

        if ($request->has('clinic_id')) {
            $overtimePermissionsQuery->when($request->clinic_id == 0, function ($query) {
                return $query->whereNull('clinic_id');
            }, function ($query) use ($request) {
                return $query->where('clinic_id', $request->clinic_id);
            });
        }

        if ($request->has('user_id')) {
            $overtimePermissionsQuery->where('user_id', $request->user_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $overtimePermissionsQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $paginate = true;
        if ($request->has('paginate')) {
            $paginate = $request->input('paginate');
        }

        $perPage = $request->input('per_page', 10);

        if ($paginate) {
            $overtimePermission = $overtimePermissionsQuery->paginate($perPage);
        } else {
            $overtimePermission = $overtimePermissionsQuery->get();
        }

        if ($request->has('group_by') && $request->input('group_by') == 'date') {
            $groupedData = $overtimePermission->groupBy('date');

            $formattedData = $groupedData->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'total_requests' => $items->count(),
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'user_id' => $item->user_id,
                            'clinic_id' => $item->clinic_id,
                            'leave_type_id' => $item->leave_type_id,
                            'date' => $item->date,
                            'start_time' => $item->start_time,
                            'end_time' => $item->end_time,
                            'reason' => $item->reason,
                            'attachment' => $item->attachment,
                            'status' => $item->status,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'user' => $item->user,
                        ];
                    }),
                ];
            });

            if ($paginate) {
                $overtimePermission->setCollection($formattedData->values());
            } else {
                $overtimePermission = $formattedData->values();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Overtime permission retrieved.',
            'data' => $overtimePermission,
        ]);
    }

    public function store(StoreOvertimePermissionRequest $request)
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

        $overtimePermission = new OvertimePermission();
        $overtimePermission->user_id = $user->id;
        $overtimePermission->date = $validated['date'];
        $overtimePermission->start_time = $validated['start_time'];
        $overtimePermission->end_time = $validated['end_time'];
        $overtimePermission->reason = $validated['reason'];
        $overtimePermission->clinic_id = $clinicID;
        $overtimePermission->status = "pending";

        $path = PermissionHelper::uploadAttachment($user->id, $request->file('attachment'), 'permission/overtime');
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
