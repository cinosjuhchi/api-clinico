<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ClinicDataController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();        
        $clinic = Clinic::with([
            'rooms',
            'location',
            'schedule',
            'services',            
            'doctors.category'
        ])
        ->where('user_id', $user->id)
        ->firstOrFail();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetch profile success',
            'data' => new ClinicResource($clinic)
        ]);
    }

    public function medicines(Request $request)
    {
        $user = Auth::user();
        $clinic = Clinic::where('user_id', $user->id)->firstOrFail();                
        $medicines = $clinic->medications;
        return response()->json($medicines);
    }

    public function pendingAppointmentsDoctor(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;        
        if(!$doctor)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
        }
        $appointments = $doctor->pendingAppointments()->paginate(5);

        return response()->json($appointments);
    }

    public function storeDoctor(Request $request)
    {
        
    }
}