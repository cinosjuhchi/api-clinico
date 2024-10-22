<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DemographicInformation;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\StoreProcedureRequest;
use App\Http\Requests\UpdateProcedureRequest;

class ProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        if(!$clinic)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
        }        
        $procedure = $clinic->procedures()->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $procedure
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
    public function store(StoreProcedureRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                Procedure::create($validated);
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
    public function show(Procedure $procedure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Procedure $procedure)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProcedureRequest $request, Procedure $procedure)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Procedure $procedure)
    {
        try {
            // Hapus pasien dari database
            $procedure->delete();

            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Procedure deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
