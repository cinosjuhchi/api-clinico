<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmergencyContact;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class EmergencyContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                EmergencyContact::create($validated);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create emergency contact'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmergencyContact $emergencyContact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmergencyContact $emergencyContact)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'relationship' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $emergencyContact->update([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'relationship' => $validated['relationship']
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success to update data.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail to update the data.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmergencyContact $emergencyContact)
    {
        //
    }
}
