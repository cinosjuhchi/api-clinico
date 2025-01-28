<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeavePermissionRequest;
use App\Models\LeaveBalance;
use App\Models\LeavePermission;
use App\Models\LeaveType;
use App\Models\LeaveTypeDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeavePermissionController extends Controller
{
    public function index(Request $request)
    {
        $leavePermissionQuery = LeavePermission::with([
            'user',
            'leaveType',
        ]);

        $user = Auth::user();
        $role = $user->role;

        $relations = match ($role) {
            'doctor', 'clinic', 'staff' => ['user.doctor.category', 'user.doctor.employmentInformation', 'user.staff.employmentInformation'],
            'admin', 'superadmin' => ['user.adminClinico.employmentInformation'],
            default => abort(401, 'Unauthorized access. Invalid role.')
        };

        $leavePermissionQuery->with($relations);

        if ($request->has('status')) {
            $statuses = $request->status;

            if (is_array($statuses)) {
                $leavePermissionQuery->whereIn('status', $statuses);
            } else {
                $leavePermissionQuery->where('status', $statuses);
            }
        }

        if ($request->has('clinic_id')) {
            $leavePermissionQuery->when($request->clinic_id == 0, function ($query) {
                return $query->whereNull('clinic_id');
            }, function ($query) use ($request) {
                return $query->where('clinic_id', $request->clinic_id);
            });
        }

        if ($request->has('user_id')) {
            $leavePermissionQuery->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $leavePermissionQuery->where(function ($query) use ($request) {
                $query->whereBetween('date_from', [$request->date_from, $request->date_to])
                    ->orWhereBetween('date_to', [$request->date_from, $request->date_to]);
            });
        } elseif ($request->has('date_from')) {
            $leavePermissionQuery->where('date_from', '>=', $request->date_from);
        } elseif ($request->has('date_to')) {
            $leavePermissionQuery->where('date_to', '<=', $request->date_to);
        }

        $paginate = true;
        if ($request->has('paginate')) {
            $paginate = $request->input('paginate');
        }

        $perPage = $request->input('per_page', 10);

        if ($paginate) {
            $leavePermissions = $leavePermissionQuery->paginate($perPage);
        } else {
            $leavePermissions = $leavePermissionQuery->get();
        }

        if ($request->has('group_by') && $request->input('group_by') == 'date_from') {
            $groupedData = $leavePermissions->groupBy('date_from');

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
                            'date_from' => $item->date_from,
                            'date_to' => $item->date_to,
                            'reason' => $item->reason,
                            'attachment' => $item->attachment,
                            'status' => $item->status,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'user' => $item->user,
                            'leave_type' => $item->leave_type,
                        ];
                    }),
                ];
            });

            if ($paginate) {
                $leavePermissions->setCollection($formattedData->values());
            } else {
                $leavePermissions = $formattedData->values();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Leave permission retrieved.',
            'data' => $leavePermissions,
        ]);
    }

    public function store(StoreLeavePermissionRequest $request)
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

        // Jika bukan annual leave, validasi attachment
        if ($validated['leave_type_id'] != 1) {
            if (!$request->hasFile('attachment')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attachment is required.',
                ]);
            }
        }

        // Cek leave type detail id untuk dapet id
        $leaveTypeDetail = LeaveTypeDetail::where('clinic_id', $clinicID)
                            ->where('leave_type_id', $validated['leave_type_id'])
                            ->first();

        if (!$leaveTypeDetail) {
            return response()->json([
               'status' => 'error',
               'message' => 'Leave type detail not found.',
            ]);
        }

        // cek sisa saldo
        $leaveBalance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_detail_id', $leaveTypeDetail->id)
            ->first();

        if (!$leaveBalance) {
            DB::beginTransaction();
            try {
                $leaveTypeDetailByClinicID = LeaveTypeDetail::where('clinic_id', $clinicID)->get();

                foreach ($leaveTypeDetailByClinicID as $typeDetail) {
                    LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_detail_id' => $typeDetail->id,
                        'bal' => $typeDetail->year_ent,
                    ]);
                }
                DB::commit();

                 $leaveBalance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_detail_id', $leaveTypeDetail->id)
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

        if ($request->hasFile('attachment')) {
            $path = PermissionHelper::uploadAttachment($user->id, $request->file('attachment'), 'permission/leave');
            $leavePermission->attachment = $path;
        }

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

        // get leave type detail by clinid_id and leave type id
        $leaveTypeDetail = LeaveTypeDetail::where('clinic_id', $leavePermission->clinic_id)
                                        ->where('leave_type_id', $leavePermission->leave_type_id)
                                        ->first();

        if (!$leaveTypeDetail) {
            return response()->json([
               'status' => 'error',
               'message' => 'Leave type detail not found.',
            ]);
        }

        $leaveBalance = LeaveBalance::where('user_id', $leavePermission->user_id)
            ->where('leave_type_detail_id', $leaveTypeDetail->id)
            ->first();

        $dateFrom = Carbon::parse($leavePermission->date_from);
        $dateTo = Carbon::parse($leavePermission->date_to);
        $requestedDays = $dateFrom->diffInDays($dateTo) + 1;

        $leaveBalance->bal = $leaveBalance->bal - $requestedDays;
        $leaveBalance->taken = $leaveBalance->taken + $requestedDays;
        $leaveBalance->save();

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
