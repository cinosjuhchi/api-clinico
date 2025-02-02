<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicServiceRequest;
use App\Http\Requests\UpdateClinicServiceRequest;
use App\Models\ClinicService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function doctorResource(Request $request)
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        if (! $doctor) {

            return response()->json([
                'status'  => 'failed',
                'message' => 'user not found',
            ]);

        }

        $clinic = $doctor->clinic;

        // Mengambil data obat berdasarkan clinic dan melakukan pencarian jika parameter 'q' ada
        $services = $clinic->services()->with(['category'])->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetched data',
            'data'    => $services,
        ]);
    }

    public function index(Request $request)
    {
        $user   = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        $query = $request->input('q');

        $clinicServices = $clinic->services()
            ->with('category') // Eager load category relation
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('price', 'like', "%{$query}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($query) {
                            $categoryQuery->where('name', 'like', "%{$query}%");
                        });
                });
            })
            ->paginate(10);

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetch data',
            'data'    => $clinicServices,
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
    public function store(StoreClinicServiceRequest $request)
    {
        $validated = $request->validated();
        $user      = Auth::user();
        $clinic    = $user->clinic;

        if (! $clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Clinic not found.',
            ], 403);
        }

        try {
            DB::transaction(function () use ($validated, $clinic) {
                $clinic->services()->create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Clinic service created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create clinic service. Please try again later.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicService $clinicService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClinicService $clinicService)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClinicServiceRequest $request, ClinicService $clinicService)
    {
        $validated = $request->validated();
        $clinicService->fill($validated);
        if ($clinicService->isDirty()) {
            DB::beginTransaction();
            try {
                $clinicService->save();
                DB::commit();
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Clinic service updated successfully.',
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to update clinic service. Please try again later.',
                ], 500);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'No changes made.',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicService $clinicService)
    {
        try {
            $clinicService->delete();
            return response()->json([
                'status'  => 'success',
                'message' => 'Clinic service deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete clinic service. Please try again later.',
            ], 500);
        }
    }
}
