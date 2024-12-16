<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BackOfficeRequest;

class BackOfficeController extends Controller
{
    public function login(BackOfficeRequest $request)
    {
        $request->validated();
        if(Auth::attempt(['email' => $request->user, 'password' => $request->password]) || Auth::attempt(['phone_number' => $request->user, 'password' => $request->password]))
        {
            $user = Auth::user();
            if($user->role == 'superadmin')
            {
                $token = $user->createToken('Clinico', ['superadmin', 'hasAccessResource', 'backOffice'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);

            }
            if($user->role == 'admin')
            {
                $token = $user->createToken('Clinico', ['admin', 'hasAccessResource', 'backOffice'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);
            }
        }

            return response()->json(["message" => "User didn't exist!"], 404);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Logout"], 200);
    }

    public function me()
    {
        $user = Auth::user()->load('staff.demographic', 'staff.contributionInfo', 'staff.employmentInformation', 'staff.financialInformation');
        return response()->json([[
            'status' => 'success',
            'message' => 'get current user',
            'data' => $user
        ]]);
    }
}
