<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClockInRequest;
use App\Models\Attendance;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with('user.doctor.clinic');

        // filter: by user_id
        $userId = $request->input('user_id');
        if ($userId) {
            $attendances = $attendances->where('user_id', $userId);
        }

        // filter: by clinic_id
        $clinicId = $request->input('clinic_id');
        if ($clinicId) {
            $attendances = $attendances->whereHas('user.doctor.clinic', function ($query) use ($clinicId) {
                $query->where('id', $clinicId);
            });
        }

        // filter: by is_late
        $isLate = $request->input('is_late');
        if ($isLate !== null) {
            $attendances = $attendances->where('is_late', $isLate);
        }

        // execute
        $attendances = $attendances->get();

        return response()->json([
            "status" => "success",
            "message" => "Attendances retrieved successfully",
            "data" => $attendances
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load('user.doctor.clinic');
        return response()->json([
            "status" => "success",
            "message" => "Attendance retrieved successfully",
            "data" => $attendance
        ]);
    }

    public function clockIn(ClockInRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // validasi: apakah user memiliki schedule dihari ini
        $userSchedule = match ($user->role) {
            'doctor' => $user->doctor->schedules->where('day', strtolower(now()->format('l')))->first(),
            'staff' => $user->staff->clinic->schedule,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$userSchedule) {
            return response()->json([
                "status" => "error",
                "message" => "You have no schedule today",
            ], 400);
        }

        // validasi: apakah user pernah absen dihari ini
        $existingAttendance = AttendanceHelper::getAttendanceByClockIn($userId);
        if ($existingAttendance) {
            return response()->json([
                "status" => "error",
                "message" => "You have already clock in today",
                "data" => $existingAttendance
            ], 400);
        }

        // validasi: apakah jarak user lebih dari 1km dari klinik
        $clinicLocation = match ($user->role) {
            'clinic' => $user->clinic->location,
            'doctor' => $user->doctor->clinic->location,
            'staff' => $user->staff->clinic->location,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        $ClinicLat = $clinicLocation->latitude;
        $ClinicLong = $clinicLocation->longitude;
        $userLat = $request['latitude'];
        $userLong = $request['longitude'];

        $isGreatedThan1km = AttendanceHelper::isDistanceGreaterThan1Km($ClinicLat, $ClinicLong, $userLat, $userLong);

        if ($isGreatedThan1km) {
            return response()->json([
                "status" => "error",
                "message" => "You are too far from the clinic",
                "data" => $clinicLocation
            ], 400);
        }

        // validasi: apakah user terlambat
        $userStartTime = Carbon::createFromTimeString($userSchedule->start_time)->setDate(now()->year, now()->month, now()->day);
        $isUserLate = false;

        if (now()->greaterThan($userStartTime)) {
            $isUserLate = true;
        }

        // execute
        $attendance = Attendance::create([
            "user_id" => $userId,
            "clock_in" => now(),
            "is_late" => $isUserLate
        ]);

        return response()->json([
            "status" => "success",
            "message" => "Clock-in successful",
            "data" => $attendance,
        ]);
    }

    public function clockOut(ClockInRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $attendance = AttendanceHelper::getAttendanceByClockIn($userId);
        $userWithClinic = User::with(['doctor.clinic.location', 'doctor.schedules', 'clinic', 'doctor'])->find($userId);

        // validasi: apakah user pernah absen dihari ini
        if (!$attendance) {
            return response()->json([
                "status" => "error",
                "message" => "You have not clocked in yet",
                "data" => $attendance
            ], 400);
        }

        // validasi: apakah user sudah clock out
        if ($attendance->clock_out) {
            return response()->json([
                "status" => "error",
                "message" => "You have already clocked out today",
                "data" => $attendance
            ], 400);
        }

        // validasi: apakah jarak user lebih dari 1km dari klinik
        $userClinicLat = $userWithClinic->doctor->clinic->location->latitude;
        $userClinicLong = $userWithClinic->doctor->clinic->location->longitude;
        $userAttendanceLat = $request['latitude'];
        $userAttendanceLong = $request['longitude'];

        $isGreatedThan1km = AttendanceHelper::isDistanceGreaterThan1Km($userClinicLat, $userClinicLong, $userAttendanceLat, $userAttendanceLong);

        if ($isGreatedThan1km) {
            return response()->json([
                "status" => "error",
                "message" => "You are too far from the clinic",
            ], 400);
        }

        // validasi: apakah user clock-out terlalu cepat
        $userSchedule = match ($user->role) {
            'doctor' => $user->doctor->schedules->where('day', strtolower(now()->format('l')))->first(),
            'staff' => $user->staff->clinic->schedule,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        if (!$userSchedule) {
            return response()->json([
                "status" => "error",
                "message" => "Schedule not found for the user.",
            ], 404);
        }

        $userEndTime = Carbon::parse($userSchedule->end_time);
        if (!now()->lessThan($userEndTime)) {
            return response()->json([
                "status" => "error",
                "message" => "Cannot clock out before the scheduled end time.",
            ], 400);
        }

        $totalWorkingHours = Carbon::parse($attendance->clock_in)->diffInMinutes(now()) / 60;
        // NOTE: seharusnya totalWorkingHours dikurang durasi breakTime (inHour), tetapi breakTime belum dibuat

        $attendance->update([
            "clock_out" => now(),
            "total_working_hours" => $totalWorkingHours,
        ]);

        return response()->json([
            "status" => "success",
            "message" => "Clock-out successful",
            "data" => $attendance,
        ]);
    }
}
