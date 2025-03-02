<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use App\Helpers\ClinicHelper;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ClinicResource;
use Illuminate\Support\Facades\Validator;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required_with:longitude|numeric',
            'longitude' => 'required_with:latitude|numeric',
            'radius' => 'numeric',
            'search' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $perPage = 3;
        $clinics = Clinic::with([
            'moh',
            'doctors.category',
            'doctors.doctorSchedules',
            'rooms',
            'location',
            'schedule',
        ])->where('status', true);

        // If coordinates provided, apply nearby filter
        if ($request->filled(['latitude', 'longitude'])) {
            $radius = $request->input('radius', 5000);
            $clinics = $clinics->join('clinic_locations', 'clinic_locations.clinic_id', '=', 'clinics.id')
                ->whereRaw(ClinicHelper::nearbyClinic(
                    $request->latitude,
                    $request->longitude,
                    $radius
                ));
        }

        // Apply search filter if provided
        if ($request->has('search')) {
            $clinics = $clinics->where('name', 'like', "%{$request->search}%");
        }

        $clinics = $clinics->paginate($perPage);

        return ClinicResource::collection($clinics)
            ->additional([
                'status' => 'success',
                'message' => 'Success to get clinic data.',
                'nextPage' => $clinics->hasMorePages() ? $clinics->currentPage() + 1 : null,
                'totalPages' => $clinics->lastPage(),
            ]);
    }

    public function clinicInformation(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found',
            ], 404);

        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch clinic data',
            'data' => $clinic->load(['user']),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        // Ambil parameter hari dari request
        $day = request()->input('day');
        $date = request()->input('date');
        $clinic = Clinic::with([
            // Cari klinik berdasarkan slug
            'rooms',
            'location',
            'schedule',
            'images',
            'appointments' => function ($query) use ($date) {
                // Hanya ambil appointment yang memiliki tanggal hari ini
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'consultation')
                ;
            },
            'services',
            'doctors' => function ($query) use ($day) {
                // Hanya ambil dokter yang memiliki jadwal sesuai dengan hari yang diminta
                $query->whereHas('doctorSchedules', function ($q) use ($day) {
                    $q->where('day', $day);
                })->with('category'); // Pastikan kategori dokter juga dimuat
            },
        ])
            ->where('slug', $slug)
            ->firstOrFail(); // Menggunakan firstOrFail untuk mendapatkan klinik atau menghasilkan 404

        // Kembalikan resource klinik dengan tambahan status dan pesan
        return response()->json([
            'status' => 'success',
            'message' => 'Success to get clinic data.',
            'data' => new ClinicResource($clinic),
        ]);
    }

    public function nearby(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius', 5000); // default = 5000m

        $clinics = Clinic::with(['location', 'schedule'])
            ->join('clinic_locations', 'clinic_locations.clinic_id', '=', 'clinics.id')
            ->where('clinics.status', true)
            ->whereRaw(ClinicHelper::nearbyClinic($latitude, $longitude, $radius))
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Success to get clinic data.',
            'data' => $clinics,
        ]);
    }
}
