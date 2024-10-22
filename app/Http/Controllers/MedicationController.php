<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationRecord;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreMedicationRequest;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        $medicines = $clinic->medications->paginate(10);        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $medicines
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicationRequest $request)
    {
        // Validasi data yang dikirim dari request
        $validated = $request->validated();

        try {
            // Menggunakan DB transaction untuk menjaga integritas data
            DB::transaction(function () use ($validated) {
                MedicationRecord::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully stored.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationRecord $medicationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationRecord $medicationRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationRecord $medicationRecord)
    {
        //
    }
}
