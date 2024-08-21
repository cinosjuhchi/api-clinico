<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\DoctorAuthLogin;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class DoctorAuthController extends Controller
{
    public function login(DoctorAuthLogin $request)
    {
        $request->validated();    

        if(Auth::guard('doctor')->attempt(['email' => $request->user, 'password' => $request->password]) || Auth::guard('doctor')->attempt(['name' => $request->user, 'password' => $request->password]))
        {
            $user = Auth::guard('doctor')->user();
            $token = $user->createToken('Clinico', ['doctor'])->plainTextToken;
    
            return response()->json([$user, 'token' => $token], 200);
        }
        
            return response()->json(["message" => "User didn't exist!"], 401);
    }
}
