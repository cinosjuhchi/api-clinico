<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Patient;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthLogin;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
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
            return response()->json([$user, 'token' => $token], 200);
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
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->phone_number = $validated['phone_number'];
        $user->save();
        $patient = new Patient();
        $patient->name = $validated['name'];
        $patient->birth_date = $request->birth_date;
        $patient->gender = $request->gender;
        $patient->address = $request->address;
        $patient->country = $request->country;
        $patient->postal_code = $request->postal_code;
        $patient->nric = $request->nric;
        $patient->email = $validated['email'];
        $patient->phone_number = $validated['phone_number'];
        $patient->save();   
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $user->id]
        );
    
        // Kirim email verifikasi
        Mail::to($user->email)->send(new VerifyEmail(['name' => $user->name, 'verification_url' => $verificationUrl]));
        return response()->json(['message' => 'Register Success', 'Patient' => $patient], 201);
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

        return response()->json(['message' => 'Email has been verified.']);
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent.']);
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
