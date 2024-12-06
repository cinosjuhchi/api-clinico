<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicLocationRequest;
use App\Http\Requests\UpdateClinicLocationRequest;
use App\Models\ClinicLocation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicLocationController extends Controller
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
    public function store(StoreClinicLocationRequest $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        $validated = $request->validated();
        try {
            DB::beginTransaction();
            $clinic->location()->create([
                'longitude' => $validated['longitude'],
                'latitude' => $validated['latitude'],
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success to store location!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicLocation $clinicLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicLocation $clinicLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicLocationRequest $request, ClinicLocation $clinicLocation)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Mengisi data model dengan data yang sudah divalidasi
            $clinicLocation->fill($validated);

            // Memeriksa apakah ada perubahan pada data model
            if ($clinicLocation->isDirty()) {
                $clinicLocation->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Clinic Location successfully updated!',
                ], 200);
            } else {
                DB::rollBack();

                return response()->json([
                    'status' => 'success',
                    'message' => 'No changes detected. Clinic Location not updated.',
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500); // Menambahkan kode status 500 untuk error internal server
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicLocation $clinicLocation)
    {
        //
    }
}
