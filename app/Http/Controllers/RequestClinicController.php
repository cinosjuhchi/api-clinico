<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Referral;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\ClinicResource;
use App\Http\Requests\StoreClinicRequest;
use App\Notifications\SetUpProfileNotification;

class RequestClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 5; // Set the number of clinics per page
        $page = $request->input('page', 1); // Get the page number from the request
        $clinics = Clinic::with([
            'doctors.category',
            'doctors.schedules',
            'rooms',
            'location', 
            'schedule'
        ])
        ->where('status', false)
        ->paginate($perPage);

        return ClinicResource::collection($clinics)
            ->additional([
                'status' => 'success',
                'message' => 'Success to get clinic data.',
                'nextPage' => $clinics->hasMorePages() ? $clinics->currentPage() + 1 : null,
                'totalPages' => $clinics->lastPage(),
            ]);
    }



    /**
     * Store a newly created resource in storage.
     */
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
                'status' => true,
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

            try {
                $user->notify(new SetUpProfileNotification());
            } catch (\Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Register Successful'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Registration failed'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Clinic $clinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinic $clinic)
    {
        DB::beginTransaction();
        try {
            $clinic->update([
                'status' => true
            ]);

            DB::commit();            
            return response()->json([
                'status' => 'success',
                'message' => 'Success to update clinic data.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update clinic data.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinic $clinic)
    {
        DB::beginTransaction();
        try {
            $clinic->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Clinic Deleted!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'error something wrong happened'
            ], 500);
        }
    }
}
