<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ChronicHealthRecord;
use App\Models\Patient;

class ChronicController extends Controller
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
        // Validasi input dari request
        $validated = $request->validate([
            'chronic_medical' => 'required|string|max:125',
            'father_chronic_medical' => 'nullable|string|max:125',
            'mother_chronic_medical' => 'nullable|string|max:125',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            // Jalankan transaksi database
            DB::transaction(function () use ($validated) {
                ChronicHealthRecord::create($validated);
            });

            // Berikan respons berhasil
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored the chronic health record.',
            ], 201);
        } catch (\Exception $e) {
            // Berikan respons jika terjadi kesalahan pada transaksi
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store the chronic health record. Please try again.',
                'error' => $e->getMessage(), // Hanya untuk debugging, hapus di produksi
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(ChronicHealthRecord $chronicHealthRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'chronics' => 'required|array',
            'chronics.*.chronic_medical' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $patient->chronics()->delete();
            foreach ($validated['chronics'] as $item) {
                $patient->chronics()->create([
                    'chronic_medical' => $item['chronic_medical']
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success update data.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail update the data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChronicHealthRecord $chronicHealthRecord)
    {
        //
    }
}
