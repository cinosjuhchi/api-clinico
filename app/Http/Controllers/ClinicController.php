<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\ClinicResource;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 3; // Set the number of clinics per page
        $page = $request->input('page', 1); // Get the page number from the request
        $clinics = Clinic::with([
            'doctors.category',
            'doctors.schedules',
            'rooms',
            'location', 
            'schedule'
        ])->paginate($perPage);

        return ClinicResource::collection($clinics)
            ->additional([
                'status' => 'success',
                'message' => 'Success to get clinic data.',
                'nextPage' => $clinics->hasMorePages() ? $clinics->currentPage() + 1 : null,
                'totalPages' => $clinics->lastPage(),
            ]);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
            'appointments' => function ($query) use ($date) {
                // Hanya ambil appointment yang memiliki tanggal hari ini
                $query->whereDate('appointment_date', $date)
                ->where('status', 'pending')
                ;
            },
            'services',
            'doctors' => function ($query) use ($day) {
                // Hanya ambil dokter yang memiliki jadwal sesuai dengan hari yang diminta
                $query->whereHas('schedules', function ($q) use ($day) {
                    $q->where('day', $day);
                })->with('category'); // Pastikan kategori dokter juga dimuat
            }
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







    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinic $clinic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinic $clinic)
    {
        //
    }
}
