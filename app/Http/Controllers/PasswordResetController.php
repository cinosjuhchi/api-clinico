<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePasswordResetRequest;
use App\Http\Requests\UpdatePasswordResetRequest;
use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

        // Simpan token reset
        PasswordReset::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(1),
            'is_used' => false,
        ]);

        Mail::to($user->email)->send(new ResetPassword([
            'resetLink' => url(env('WEB_CLINICO_URL') . '/reset-password/{$token}'),
            'email' => $user->email,
        ]));

        return response()->json([
            'message' => 'Link reset password telah dikirim',
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
        //
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
