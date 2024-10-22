<?php

namespace App\Http\Controllers;

use App\Models\Injection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInjectionRequest;
use App\Http\Requests\UpdateInjectionRequest;

class InjectionController extends Controller
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
    public function store(StoreInjectionRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                Injection::create($validated);
            });             
            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully stored.',
            ], 201);
        } catch(\Exception $e) {
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
    public function show(Injection $injection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Injection $injection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInjectionRequest $request, Injection $injection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Injection $injection)
    {
        //
    }
}
