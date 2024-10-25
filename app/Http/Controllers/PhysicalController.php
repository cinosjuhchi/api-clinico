<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\PhysicalExamination;

class PhysicalController extends Controller
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
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'blood_type' => 'nullable|enum:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            // Jalankan transaksi database
            DB::transaction(function () use ($validated) {
                PhysicalExamination::create($validated);
            });

            // Berikan respons berhasil
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored the physical examination record.',
            ], 201);
        } catch (\Exception $e) {
            // Berikan respons jika terjadi kesalahan pada transaksi
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store the physical examination record. Please try again.',
                'error' => $e->getMessage(), // Hanya untuk debugging, hapus di produksi
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(PhysicalExamination $physicalExamination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhysicalExamination $physicalExamination)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhysicalExamination $physicalExamination)
    {
        //
    }
}
