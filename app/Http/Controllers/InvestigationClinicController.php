<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\InvestigationClinic;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreInvestigationClinicRequest;
use App\Http\Requests\UpdateInvestigationClinicRequest;

class InvestigationClinicController extends Controller
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
                'message' => 'Clinic not found'
            ], 403);
        }
        $query = $request->input('q');
        $investigations = $clinic->investigations()
        ->with('items')
        ->withCount('items') // Count the related items
        ->when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%") // Search by investigation name
              ->orHaving('items_count', '=', $query); // Search by items count
        })
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched data',
            'data' => $investigations
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
    public function store(StoreInvestigationClinicRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $clinic = $user->clinic;
        if(!$clinic)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'Clinic not found'
            ], 403);
        }
        DB::beginTransaction();
        try {
            $investigation = $clinic->investigations()->create([
                'name' => $validated['name'],
                'description' => $validated['description'],                
            ]);
            foreach ($validated['items'] as $item) {
                $investigation->items()->create([
                    'name' => $item['item_name'],                    
                    'price' => $item['price']
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Investigation clinic created successfully.'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InvestigationClinic $investigationClinic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestigationClinic $investigationClinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvestigationClinicRequest $request, InvestigationClinic $investigationClinic)
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        try {
            $investigationClinic->update([
                'name' => $validated['name'],
                'description' => $validated['description'],            
            ]);
    
            $investigationClinic->items()->delete();
            foreach ($validated['items'] as $item) {
                $investigationClinic->items()->create([
                    'name' => $item['name'],                
                    'price' => $item['price']
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Investigation clinic updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update investigation clinic. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestigationClinic $investigationClinic)
    {
        DB::beginTransaction();
        try {
            $investigationClinic->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Investigation clinic deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            //throw $th;
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete investigation clinic. Please try again later.'
            ], 500);
        }
    }
}
