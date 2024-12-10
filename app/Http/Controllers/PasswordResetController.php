<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasswordResetRequest;
use App\Http\Requests\UpdatePasswordResetRequest;
use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate unique token
        $token = Str::random(60);
        try {
            DB::beginTransaction();
            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addHours(1),
                'is_used' => false,
            ]);

            Mail::to($user->email)->send(new ResetPassword([
                'resetLink' => url(env('WEB_CLINICO_URL') . '/reset-password/' . $token),
                'email' => $user->email,
            ]));
            DB::commit();

            return response()->json([
                'message' => 'Link reset password telah dikirim',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'=>'failed',
                'message' => $e->getMessage()
            ], 500);
        }
        // Simpan token reset

    }

    public function validateResetToken($token)
    {
        $resetRequest = PasswordReset::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRequest) {
            return response()->json([
                'valid' => false,
                'message' => 'Token reset tidak valid atau sudah kedaluwarsa',
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'email' => $resetRequest->email,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePasswordResetRequest $request)
    {
        $validate = $request->validated();
        if ($validate['password'] != $validate['confirm_password']) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Please input correctly the password field same as the confirm password',
            ], 400);
        }
        $resetRequest = PasswordReset::where('token', $request->token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRequest) {
            return response()->json([
                'message' => 'Token reset tidak valid',
            ], 400);
        }
        $user = User::where('email', $resetRequest->email)->first();

        try {
            DB::beginTransaction();
            $user->update([
                'password' => bcrypt($validate['password']),
            ]);
            $resetRequest->update([
                'is_used' => true,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Password successfully updated!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update password',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordReset $passwordReset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PasswordReset $passwordReset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePasswordResetRequest $request, PasswordReset $passwordReset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordReset $passwordReset)
    {
        //
    }
}
