<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Clinic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ClinicResource;

class ClinicProfileController extends Controller
{
    public function clinicPatient(Request $request)
    {
        $user = Auth::user();
        $day = $request->input('day');
        $date = $request->input('date');
        $clinic = Clinic::with([
            // Cari klinik berdasarkan slug
            'rooms',
            'location',
            'schedule',
            'appointments' => function ($query) use ($date) {
                // Ambil semua appointment pada hari ini
                $query->whereDate('appointment_date', $date);
            },
            'pendingAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                    ->where('status', 'pending');
            },
            'consultationAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                ->where('status', 'consultation');                    
            },
            'completedAppointments' => function ($query) use ($date) {
                // Ambil hanya appointment dengan status 'pending' pada hari ini
                $query->whereDate('appointment_date', $date)
                ->where('status', 'completed');
            },
            'services',
            'doctors' => function ($query) use ($day) {
                // Hanya ambil dokter yang memiliki jadwal sesuai dengan hari yang diminta
                $query->whereHas('schedules', function ($q) use ($day) {
                    $q->where('day', $day);
                })->with('category'); // Pastikan kategori dokter juga dimuat
            }
        ])        
        ->where('user_id', $user->id)
        ->firstOrFail(); // Menggunakan firstOrFail untuk mendapatkan klinik atau menghasilkan 404

        // Kembalikan resource klinik dengan tambahan status dan pesan
        return response()->json([
            'status' => 'success',
            'message' => 'Success to get clinic data.',
            'data' => new ClinicResource($clinic),            
        ]);
    }

    
}