<?php

namespace App\Http\Controllers;

use App\Models\PanelClinic;
use Illuminate\Http\Request;
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
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        
        if (!$clinic) {
            return response()->json([
                'status' => 'not found',
                'message' => 'Clinic not found',
            ], 404);
        }
        
        $query = $clinic->panels();
        
        if (request()->has('search')) {
            $search = request()->query('search');
            $query->where('name', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%")
                ->orWhere('phone_number', 'like', "%$search%");
        }
        
        $panels = $query->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $panels
        ], 200);
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
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
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
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PanelClinic $panelClinic)
    {
        $user = Auth::user();                        
        return response()->json([
            'status' => 'success',
            'data' => $panelClinic
        ]);
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
        $validated = $request->validated();
        
        DB::beginTransaction();
        try {
            $panelClinic->update($validated);
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Panel updated successfully',
                'data' => $panelClinic
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PanelClinic $panelClinic)
    {
        $user = Auth::user();        
        
        DB::beginTransaction();
        try {
            $panelClinic->delete();
            DB::commit();            
            return response()->json([
                'status' => 'success',
                'message' => 'Panel deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resource(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };
        
        if (!$clinic) {
            return response()->json([
                'status' => 'not found',
                'message' => 'Clinic not found',
            ], 404);
        }
        
        $query = $clinic->panels();                
        
        $panels = $query->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $panels
        ], 200);
    }
    public function patientResource(Request $request)
    {
        $user = Auth::user();

        $query = PanelClinic::query();

        // Jika ada query parameter 'search', filter berdasarkan kolom 'name'
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Paginasi hasilnya
        $panels = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $panels
        ], 200);
    }

}
