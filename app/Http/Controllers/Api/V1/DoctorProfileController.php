<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DoctorProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;        
        $doctor = Doctor::with([
            'clinic',
            'category',
            'schedules'
        ])
        ->where('user_id', $id)
        ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Fetch doctor profile is successfully!',
            'data' => new DoctorResource($doctor)
        ]);
    }
}
