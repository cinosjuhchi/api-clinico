<?php

namespace App\Http\Controllers;

use App\Models\Medication;
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
        if(!$clinic)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
        }        
        $medicines = $clinic->medications()->with(['pregnancyCategory'])->paginate(10);        
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
                Medication::create($validated);
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
    public function update(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'name' => 'string|sometimes|max:255|min:3',
            'price' => 'numeric|sometimes',            
            'brand' => 'string|sometimes|max:255|min:3',
            'pregnancy_category_id' => 'sometimes|exists:pregnancy_categories,id',
            'sku_code' => 'string|sometimes|max:255|min:5',
            'paediatric_dose' => 'integer|sometimes',
            'unit' => 'string|sometimes|max:255',            
            'expired_date' => 'date|sometimes',                    
            'for' => 'string|sometimes|max:255|min:3',                    
            'manufacture' => 'string|sometimes|max:255|min:3',                    
            'supplier' => 'string|sometimes|max:255|min:3',                    
        ]);

        $medication->fill($validated);

        if ($medication->isDirty()) {
            try {
                DB::transaction(function () use ($medication) {
                    $medication->save();
                });
                return response()->json([
                    'status' => 'success',
                    'message' => 'Update successfully!'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to store data.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'info',
            'message' => 'No changes made.'
        ], 200);
    }

    public function addBatch(Request $request, Medication $medication)
    {
        $validated = $request->validate([            
            'total_amount' => 'integer|required'
        ]);
        $medication->total_amount += $validated['total_amount'];
        $medication->batch += 1;
        try {            
            DB::transaction(function () use ($medication) {
                $medication->save();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully restock'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restock data.',
                'error' => $e->getMessage(),
            ], 500);  
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationRecord $medicationRecord)
    {
        //
    }
}
