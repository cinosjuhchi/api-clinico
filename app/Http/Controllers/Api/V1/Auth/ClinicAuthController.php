<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicAuthLogin;
use App\Mail\VerifyEmail;
use App\Models\Clinic;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use App\Notifications\SetUpProfileNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ClinicAuthController extends Controller
{
    public function login(ClinicAuthLogin $request)
    {
        $request->validated();

        Log::info('Login Request:', $request->all());

        if (Auth::attempt(['email' => $request->user, 'password' => $request->password]) ||
            Auth::attempt(['phone_number' => $request->user, 'password' => $request->password])) {

            $user = Auth::user();
            Log::info('Authenticated User:', $user->toArray());

            if ($user->role == 'clinic') {
                $token = $user->createToken('Clinico', ['clinic', 'hasAccessResource'])->plainTextToken;
                return response()->json(['user' => $user, 'role' => $user->role, 'token' => $token], 200);
            }

            if ($user->role == 'doctor') {
                $token = $user->createToken('Clinico', ['doctor', 'hasAccessResource'])->plainTextToken;
                return response()->json(['user' => $user, 'role' => $user->role, 'token' => $token], 200);
            }

            if ($user->role == 'staff') {
                $token = $user->createToken('Clinico', ['staff', 'hasAccessResource'])->plainTextToken;
                return response()->json(['user' => $user, 'role' => $user->role, 'token' => $token], 200);
            }
        }

        Log::error("Login Failed", ['user' => $request->user]);
        return response()->json(["message" => "User didn't exist!"], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3|unique:clinics,name',
            'company' => 'required|string|max:255|min:3',
            'ssm_number' => 'required|integer',
            'registration_number' => 'required|integer',
            'referral_number' => 'nullable|string',
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

            $clinic = new Clinic();
            $clinic->name = $validated['name'];
            $clinic->company = $validated['company'];
            $clinic->ssm_number = $validated['ssm_number'];
            $clinic->registration_number = $validated['registration_number'];
            $clinic->user_id = $user->id;
            $clinic->slug = $slug;

            // $clinic = Clinic::create([
            //     'name' => $validated['name'],
            //     'company' => $validated['company'],
            //     'ssm_number' => $validated['registration_number'],
            //     'referral_number' => $validated['referral_number'],
            //     'registration_number' => $validated['registration_number'],
            //     'user_id' => $user->id,
            //     'slug' => $slug,
            // ]);

            if (!empty($validated["referral_number"])) {
                $referralCodeOwner = ReferralCode::where('code', $validated['referral_number'])->first();

                if (!$referralCodeOwner) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Referral number not found",
                        "data" => ["code" => $validated['referral_number']]
                    ], 422);
                }

                $referralCodeOwner->increment("score", 1);
                $clinic->referral_number = $validated['referral_number'];

                Referral::create([
                    'user_id' => $user->id,
                    'admin_id' => $referralCodeOwner->user_id,
                ]);
            }

            $clinic->save();

            Mail::to($user->email)->send(new VerifyEmail([
                'name' => $clinic->name,
                'verification_url' => $verificationUrl,
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
