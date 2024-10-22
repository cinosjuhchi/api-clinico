<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Family;
use App\Models\Patient;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ClinicAuthLogin;
use App\Http\Requests\DoctorAuthLogin;
use App\Models\DemographicInformation;
use App\Notifications\SetUpProfileNotification;

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
                $token = $user->createToken('Clinico', ['clinic', 'hasAccessResource'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);

            }
            if($user->role == 'doctor')
            {
                $token = $user->createToken('Clinico', ['doctor', 'hasAccessResource'])->plainTextToken;
                $role = $user->role;
                return response()->json([$user, 'role' => $role,'token' => $token], 200);
            }
        }
        
            return response()->json(["message" => "User didn't exist!"], 404);        
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3|unique:clinic',
            'company' => 'required|string|max:255|min:3',
            'ssm_number' => 'required|integer',
            'registration_number' => 'required|integer',
            'referral_number' => 'required|integer',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|min:10|unique:users',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'],
                'role' => 'clinic',
            ]);            
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', now()->addMinutes(60), ['id' => $user->id]
            );  

            $slug = Str::slug($validated['name']);

            $clinic = Clinic::create([
                'name' => $validated['name'],
                'company' => $validated['company'],
                'ssm_number' => $validated['registration_number'],
                'referral_number' => $validated['referral_number'],                
                'registration_number' => $validated['registration_number'],
                'user_id' => $user->id,
                'slug' => $slug
            ]);

            Mail::to($user->email)->send(new VerifyEmail([
                'name' => $clinic->name,
                'verification_url' => $verificationUrl
            ]));
            
            try {
                $user->notify(new SetUpProfileNotification());
            } catch (\Exception $e) {                
                Log::error('Notification error: ' . $e->getMessage());
            }                        
        });
        return response()->json(['status' => 'success', 'message' => 'Register Successful'], 201);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();        
        return response()->json(["message" => "Logout"], 200);
    }
}
