<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ClinicAuthLogin;
use App\Http\Requests\DoctorAuthLogin;

class ClinicAuthController extends Controller
{
    public function login(ClinicAuthLogin $request)
    {
        $request->validated();    

        if(Auth::guard('clinic')->attempt(['email' => $request->user, 'password' => $request->password]) || Auth::guard('clinic')->attempt(['name' => $request->user, 'password' => $request->password]))
        {
            $user = Auth::guard('clinic')->user();
            $token = $user->createToken('Clinico', ['clinic'])->plainTextToken;
    
            return response()->json([$user, 'token' => $token], 200);
        }
        
            return response()->json(["message" => "User didn't exist!"], 401);        
    }
}
