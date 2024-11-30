<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\ClinicImage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ClinicProfileRequest;
use App\Http\Requests\StoreClinicImageRequest;
use App\Http\Requests\UpdateClinicImageRequest;

class ClinicImageController extends Controller
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
    public function store(StoreClinicImageRequest $request, Clinic $clinic)
    {
        $validated = $request->validated();

        if ($clinic->images()->count() + count($validated['images']) > 3) {
            return response()->json([
                'message' => 'Clinic cannot have more than 3 images',
            ], 400);
        }

        foreach ($validated['images'] as $index => $image) {
            // Store the image
            $imagePath = $image->store('clinic_image');

            // Create the new image record
            $clinic->images()->create([
                'image_path' => $imagePath,                
            ]);
        }

        return response()->json([
            'message' => 'Images added successfully',
            'data' => $clinic->images,
        ], 200);
    }

    public function storeProfile(ClinicProfileRequest $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        if(!$clinic)
        {
            return response()->json([
                'status' => 'Not Found',
                "message"=> "Clinic not found"
            ], 404);
        }
        $validated = $request->validated();   
        try {
            DB::beginTransaction();
            $clinic->update([
                'image_profile' => $validated['image']->store('clinic_profile'),
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully update profile'
            ], 200);
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
    public function show(ClinicImage $clinicImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicImage $clinicImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicImageRequest $request, ClinicImage $clinicImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicImage $clinicImage)
    {
        //
    }
}
