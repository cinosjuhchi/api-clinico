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
}
