<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicAuthLogin;
use App\Http\Requests\StoreClinicRequest;
use App\Mail\VerifyEmail;
use App\Models\Clinic;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use App\Notifications\NewClinicNotification;
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

    public function store(StoreClinicRequest $request)
    {
        DB::beginTransaction();
        try {
            $referralCodeOwner = null;
            if (!empty($request["referral_number"])) {
                $referralCodeOwner = ReferralCode::where('code', $request['referral_number'])->first();

                if (!$referralCodeOwner) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Referral number not found",
                        "data" => ["code" => $request['referral_number']]
                    ], 422);
                }
            }

            $user = User::create([
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
                'phone_number' => $request['phone_number'],
                'role' => 'clinic',
            ]);

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', now()->addMinutes(60), ['id' => $user->id]
            );

            $clinic = Clinic::create([
                'name' => $request['name'],
                'company' => $request['company'],
                'ssm_number' => $request['ssm_number'],
                'registration_number' => $request['registration_number'],
                'user_id' => $user->id,
                'slug' => Str::slug($request['name']),
                'referral_number' => $referralCodeOwner ? $request['referral_number'] : null,
            ]);

            if ($referralCodeOwner) {
                $referralCodeOwner->increment("score", 1);

                Referral::create([
                    'user_id' => $user->id,
                    'admin_id' => $referralCodeOwner->user_id,
                ]);
            }

            Mail::to($user->email)->send(new VerifyEmail([
                'name' => $clinic->name,
                'verification_url' => $verificationUrl,
            ]));
            $url = "https://clinic.clinico.site/clinic/profile";
            try {
                $user->notify(new SetUpProfileNotification($url));
            } catch (\Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }

            DB::commit();
            try {
                $title = "New Clinic";
                $type = "info";
                $message = "New clinic has been registered and is awaiting approval.";
            
                // Ambil semua user dengan role 'superadmin' atau 'admin'
                $adminUsers = User::whereIn('role', ['superadmin', 'admin'])->get();
            
                foreach ($adminUsers as $user) {
                    $user->notify(new NewClinicNotification($title, $message, $type));
            
                    $latestNotification = $user->notifications()->latest()->first();
                    if ($latestNotification) {
                        $latestNotification->update([
                            'expired_at' => now()->addDay(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }
            

            return response()->json(['status' => 'success', 'message' => 'Register Successful'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Registration failed'], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Logout"], 200);
    }
}
