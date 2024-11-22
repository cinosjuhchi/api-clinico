<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInjectionRequest;
use App\Models\Injection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InjectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function doctorResource(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {

            return response()->json([
                'status' => 'failed',
                'message' => 'user not found',
            ]);

        }

        $clinic = $doctor->clinic;

        // Mengambil data obat berdasarkan clinic dan melakukan pencarian jika parameter 'q' ada
        $injections = $clinic->injections()->with(['pregnancyCategory'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched data',
            'data' => $injections,
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            $clinic = $user->doctor->clinic;
            if (!$clinic) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user not found',
                ]);
            }

        }
        $query = $request->input('q');
        $injections = $clinic->injections()->with(['pregnancyCategory'])
            ->when($query, function ($q, $query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku_code', 'like', "%{$query}%")
                    ->orWhere('price', 'like', "%{$query}%")
                    ->orWhere('expired_date', 'like', "%{$query}%");
            })
            ->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $injections,
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
        } catch (\Exception $e) {
            DB::rollBack();
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
    public function update(Request $request, Injection $injection)
    {
        $validated = $request->validate([
            'name' => 'string|sometimes|max:255|min:3',
            'price' => 'numeric|sometimes',
            'brand' => 'string|sometimes|max:255|min:3',
            'pregnancy_category_id' => 'sometimes|exists:pregnancy_categories,id',
            'sku_code' => 'string|sometimes|max:255|min:5',
            'paediatric_dose' => 'integer|sometimes',
            'unit' => 'string|sometimes|max:255',
            'expired_date' => 'date|sometimes',
        ]);

        $injection->fill($validated);

        if ($injection->isDirty()) {
            try {
                DB::transaction(function () use ($injection) {
                    $injection->save();
                });
                return response()->json([
                    'status' => 'success',
                    'message' => 'Update successfully!',
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to store data.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'info',
            'message' => 'No changes made.',
        ], 200);
    }

    public function addBatch(Request $request, Injection $injection)
    {
        $validated = $request->validate([
            'total_amount' => 'integer|required',
        ]);
        $injection->total_amount += $validated['total_amount'];
        $injection->batch += 1;
        try {
            DB::transaction(function () use ($injection) {
                $injection->save();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully restock',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to restock data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Injection $injection)
    {
        try {
            // Hapus pasien dari database
            $injection->delete();

            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Injection deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
