<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Family;
use App\Models\Patient;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Models\EmergencyContact;
use App\Models\MedicationRecord;
use App\Models\OccupationRecord;
use App\Models\ImmunizationRecord;
use Illuminate\Support\Facades\DB;
use App\Models\ChronicHealthRecord;
use App\Models\PhysicalExamination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthLogin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\DemographicInformation;
use Illuminate\Auth\Events\Registered;
use App\Models\EmergencyContactInformation;
use Illuminate\Validation\ValidationException;
use App\Notifications\SetUpProfileNotification;


class AuthController extends Controller
{


    /**
     * Login an user
     */
    public function login(UserAuthLogin $request)
    {
        $request->validated();        

        if(Auth::attempt(['email' => $request->user, 'password' => $request->password]) || Auth::attempt(['phone_number' => $request->user, 'password' => $request->password]))
        {
            $user = Auth::user();
            if($user->role != 'user')
            {                
                return response()->json(["message" => "User didn't exist!"], status: 404);

            }            
            $token = $user->createToken('Clinico', ['user']);
            return response()->json(['status' => 'Success', 'message' => 'Login Success', 'user' => $user, 'token' => $token], 200);
        }else{
            return response()->json(["message" => "User didn't exist!"], status: 404);
        }
                            

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'phone_number' => 'required|string|min:10|unique:users',
        'nric' => 'required|string|min:5|unique:demographic_information',
        'date_birth' => 'required|date|before:today',
        'address' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'postal_code' => 'required|numeric|digits_between:4,10',
        'gender' => 'required|string',           
        ]);

        $token = null;
        
        DB::transaction(function () use ($validated, &$token) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'],
                'role' => 'user',
            ]);            
            $token = $user->createToken('Clinico', ['user'])->plainTextToken;
            $family = Family::create([
                'user_id' => $user->id,
            ]);             

            $patient = Patient::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'user_id' => $user->id,
                'family_id' => $family->id,
            ]);
            $lastDemographicInfo = DemographicInformation::orderBy('id', 'desc')->first(); 
            $newMRN = 'MRN' . str_pad(($lastDemographicInfo ? ((int) substr($lastDemographicInfo->mrn, 3)) + 1 : 1), 7, '0', STR_PAD_LEFT);

            DemographicInformation::create([
                'date_birth' => $validated['date_birth'],
                'gender' => $validated['gender'],
                'nric' => $validated['nric'],
                'address' => $validated['address'],
                'country' => $validated['country'],
                'postal_code' => $validated['postal_code'],
                'patient_id' => $patient->id,
                'mrn' => $newMRN
            ]);
        

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', now()->addMinutes(60), ['id' => $user->id]
            );

            Mail::to($user->email)->send(new VerifyEmail([
                'name' => $patient->name,
                'verification_url' => $verificationUrl
            ]));
            try {
                $user->notify(new SetUpProfileNotification());
            } catch (\Exception $e) {                
                Log::error('Notification error: ' . $e->getMessage());
            }
            
        });
        
        return response()->json(['status' => 'success', 'message' => 'Register Successful', 'token' => $token ], 201);
    }


    // email verification
    public function verifyEmail($id, Request $request)
    {
        $user = User::find($id);
        if ($user->hasVerifiedEmail() && $request->expectsJson()) {
            return response()->json(['message' => 'Email already verified.']);
        }
        
        if($user->hasVerifiedEmail()){
            return redirect()->away(env('WEB_CLINICO_URL'))->with('message', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return redirect()->away(env('WEB_CLINICO_URL'))->with('message', 'Email already verified.');
        }
        
    }
    

    public function resendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'Success', 'message' => 'Verification email sent.'], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();        
        return response()->json(["message" => "Logout"], 200);
    }

    // end email verification


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
