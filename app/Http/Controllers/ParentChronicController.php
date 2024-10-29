<?php

namespace App\Http\Controllers;

use App\Models\ParentChronic;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreParentChronicRequest;
use App\Http\Requests\UpdateParentChronicRequest;

class ParentChronicController extends Controller
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
    public function store(StoreParentChronicRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                ParentChronic::create($validated);
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully stored parent chronic record.'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store parent chronic record.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentChronic $parentChronic)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParentChronic $parentChronic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParentChronicRequest $request, ParentChronic $parentChronic)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $parentChronic->update([
                'father_chronic_medical' => $validated['father_chronic_medical'],
                'mother_chronic_medical' => $validated['mother_chronic_medical']
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success update data.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Fail to update data.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentChronic $parentChronic)
    {
        //
    }
}
