<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\MohClinic;
use Illuminate\Http\Request;
use App\Helpers\ClinicHelper;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ClinicResource;
use App\Http\Requests\StoreMohClinicRequest;
use App\Http\Requests\UpdateMohClinicRequest;

class MohClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {        
        $perPage = 10;
        $clinics = Clinic::with([
            'user',
            'financial',
            'moh',
            'doctors.category',
            'doctors.doctorSchedules',
            'rooms',
            'location',
            'schedule',
        ])->where('is_moh', true);                

        // Apply search filter if provided
        if ($request->has('search')) {
            $clinics = $clinics->where('name', 'like', "%{$request->search}%");
        }

        $clinics = $clinics->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Retrieved Data success.',
            'data' => $clinics
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
    public function store(StoreMohClinicRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MohClinic $mohClinic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MohClinic $mohClinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMohClinicRequest $request, MohClinic $mohClinic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MohClinic $mohClinic)
    {
        //
    }
}
