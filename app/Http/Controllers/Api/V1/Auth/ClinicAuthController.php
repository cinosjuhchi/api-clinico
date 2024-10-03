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
        if(Auth::attempt(['email' => $request->user, 'password' => $request->password]) || Auth::attempt(['phone_number' => $request->user, 'password' => $request->password]))
        {
            $user = Auth::user();
            if($user->role == 'clinic')
            {
                $token = $user->createToken('Clinico', ['clinic'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);

            }
            if($user->role == 'doctor')
            {
                $token = $user->createToken('Clinico', ['doctor'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);
            }
        }
        
            return response()->json(["message" => "User didn't exist!"], 401);        
    }
}
