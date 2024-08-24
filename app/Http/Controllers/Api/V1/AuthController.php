<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Patient;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Models\EmergencyContact;
use App\Models\MedicationRecord;
use App\Models\OccupationRecord;
use App\Models\ImmunizationRecord;
use App\Models\ChronicHealthRecord;
use App\Models\PhysicalExamination;
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
            $token = $user->createToken('Clinico', ['user']);
            return response()->json(['status' => 'Success', 'message' => 'Login Success', 'token' => $token], 200);
        }
                            

        return response()->json(["message" => "User didn't exist!"], 401);
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
           'nric' => 'required|string|min:5|unique:patients',
           'date_birth' => 'required|date|before:today',
           'address' => 'required|string|max:255',
           'country' => 'required|string|max:255',
           'postal_code' => 'required|numeric|digits_between:4,10',
           'gender' => 'required|string',           
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->phone_number = $validated['phone_number'];
        $user->save();
        $demographic = new DemographicInformation();
        $demographic->date_birth = $validated['date_birth'];
        $demographic->gender = $validated['gender'];
        $demographic->nric = $validated['nric'];
        $demographic->address = $validated['address'];
        $demographic->country = $validated['country'];
        $demographic->postal_code = $validated['postal_code'];
        $demographic->user_id = $user->id;
        $demographic->save();

        $chronic = new ChronicHealthRecord();
        $chronic->user_id = $user->id;
        $chronic->save();


        $medication = new MedicationRecord();
        $medication->user_id = $user->id;
        $medication->save();

        $physical = new PhysicalExamination();
        $physical->user_id = $user->id;
        $physical->save();

        $occupation = new OccupationRecord();
        $occupation->user_id = $user->id;
        $occupation->save();

        $immunization = new ImmunizationRecord();
        $immunization->user_id = $user->id;
        $immunization->save();

        $emergency = new EmergencyContact();
        $emergency->user_id = $user->id;
        $emergency->save();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $user->id]
        );
        $data = User::with('demographic')->find($user->id);
        // Kirim email verifikasi
        Mail::to($user->email)->send(new VerifyEmail(['name' => $user->name, 'verification_url' => $verificationUrl]));
        return response()->json(['status' => 'Success', 'message' => 'Register Successful', 'data' => $data ], 201);
    }

    // email verification
    public function verifyEmail($id, Request $request)
    {
        $user = User::find($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if($request->expectsJson()){
            return response()->json(['message' => 'Email has been verified.'], 200);
        }else{
            return redirect()->to('http://localhost:5173/patient')->with('message', 'Email already verified.');
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
