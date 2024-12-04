<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicScheduleRequest;
use App\Http\Requests\UpdateClinicScheduleRequest;
use App\Models\ClinicSchedule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicScheduleController extends Controller
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
    public function store(StoreClinicScheduleRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $clinic = $user->clinic;

        try {
            DB::beginTransaction();
            $clinic->schedule()->create([
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Clinic Schedule Successfully store',
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
    public function show(ClinicSchedule $clinicSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicSchedule $clinicSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicScheduleRequest $request, ClinicSchedule $clinicSchedule)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Mengisi data model dengan data yang sudah divalidasi
            $clinicSchedule->fill($validated);

            // Memeriksa apakah ada perubahan pada data model
            if ($clinicSchedule->isDirty()) {
                $clinicSchedule->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Clinic Schedule successfully updated!',
                ], 200);
            } else {
                DB::rollBack();

                return response()->json([
                    'status' => 'success',
                    'message' => 'No changes detected. Clinic Schedule not updated.',
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
    public function destroy(ClinicSchedule $clinicSchedule)
    {
        //
    }
}
