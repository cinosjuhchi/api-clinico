<?php

namespace App\Http\Controllers;

use App\Models\PanelClinic;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePanelClinicRequest;
use App\Http\Requests\UpdatePanelClinicRequest;

class PanelClinicController extends Controller
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
    public function store(StorePanelClinicRequest $request)
    {
        $validated = $request->validated();
        $user   = Auth::user();
        $clinic = $user->clinic;
        if(!$clinic)
        {
            return response()->json([
                'status' => 'not found',
                'message' => 'Clinic not found',
            ], 404);
        }
        DB::beginTransaction();
        try {
            $clinic->panels()->create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'claim' => $validated['claim'],
                'memo' => $validated['memo'],
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Panel Created'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PanelClinic $panelClinic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PanelClinic $panelClinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePanelClinicRequest $request, PanelClinic $panelClinic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PanelClinic $panelClinic)
    {
        //
    }
}
