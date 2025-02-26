<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ClinicResource;

class RequestClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 5; // Set the number of clinics per page
        $page = $request->input('page', 1); // Get the page number from the request
        $clinics = Clinic::with([
            'doctors.category',
            'doctors.schedules',
            'rooms',
            'location', 
            'schedule'
        ])
        ->where('status', false)
        ->paginate($perPage);

        return ClinicResource::collection($clinics)
            ->additional([
                'status' => 'success',
                'message' => 'Success to get clinic data.',
                'nextPage' => $clinics->hasMorePages() ? $clinics->currentPage() + 1 : null,
                'totalPages' => $clinics->lastPage(),
            ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Clinic $clinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinic $clinic)
    {
        DB::beginTransaction();
        try {
            $clinic->update([
                'status' => true
            ]);

            DB::commit();            
            return response()->json([
                'status' => 'success',
                'message' => 'Success to update clinic data.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update clinic data.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinic $clinic)
    {
        DB::beginTransaction();
        try {
            $clinic->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Clinic Deleted!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'error something wrong happened'
            ], 500);
        }
    }
}
