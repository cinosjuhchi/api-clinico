<?php

namespace App\Http\Controllers;

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestigationClinic $investigationClinic)
    {
        //
    }
}
