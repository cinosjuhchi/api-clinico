<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ChronicHealthRecord;

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
    public function update(Request $request, ChronicHealthRecord $chronicHealthRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChronicHealthRecord $chronicHealthRecord)
    {
        //
    }
}
