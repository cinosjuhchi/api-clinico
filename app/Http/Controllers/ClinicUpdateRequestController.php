<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicUpdateRequestRequest;
use App\Http\Requests\UpdateClinicUpdateRequestRequest;
use App\Models\ClinicUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicUpdateRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function requestUpdate(StoreClinicUpdateRequestRequest $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        $validated = $request->validated();

        // Serialize the validated data
        $serializedData = json_encode($validated);

        $updateRequest = ClinicUpdateRequest::create([
            'clinic_id' => $clinic->id, // assuming using auth for clinic
            'requested_data' => $serializedData,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Update request submitted successfully',
            'data' => $updateRequest,
        ]);
    }

    public function getPendingUpdates()
    {
        $requests = ClinicUpdateRequest::with('clinic')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $requests]);
    }

    public function processUpdateRequest(Request $request, ClinicUpdateRequest $requestUpdate)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);
        try {
            DB::beginTransaction();
            $updateRequest = ClinicUpdateRequest::findOrFail($requestUpdate->id);
            $updateRequest->status = $validated['status'];
            $updateRequest->approved_by = auth()->id();
            $updateRequest->approved_at = now();
            $updateRequest->save();

            if ($validated['status'] === 'approved') {
                $clinic = $updateRequest->clinic;
                $requestedData = $updateRequest->requested_data;
                // Update clinic data
                $clinic->financial()->update([
                    'bank_name' => $requestedData['bank_name'],
                    'acc_name' => $requestedData['acc_name'],
                    'bank_account_number' => $requestedData['bank_account_number'],
                    'bank_detail' => $requestedData['bank_detail'],
                ]);

                $user = $clinic->user()->update([
                    'email' => $requestedData['email'],
                    'phone_number' => $requestedData['phone_number'],
                ]);

            }

            DB::commit();

            return response()->json([
                'message' => 'Update request processed successfully',
                'data' => $updateRequest,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process update request',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicUpdateRequest $clinicUpdateRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicUpdateRequest $clinicUpdateRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicUpdateRequestRequest $request, ClinicUpdateRequest $clinicUpdateRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicUpdateRequest $clinicUpdateRequest)
    {
        //
    }
}
