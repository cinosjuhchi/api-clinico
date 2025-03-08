<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicationRequest;
use App\Models\Medication;
use App\Models\MedicationRecord;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function doctorResource(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
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

        // Mengambil data obat berdasarkan clinic dan melakukan pencarian jika parameter 'q' ada
        $medicines = $clinic->medications()->with(['pregnancyCategory'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched data',
            'data' => $medicines,
        ]);
    }
    public function drugInPregnancy(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            $clinic = $user->doctor->clinic;
            if (!$clinic) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found',
                ], 404);
            }
        }

        // Menerima parameter pencarian 'q'
        $query = $clinic->medications()->with(['pregnancyCategory']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        // Pagination, default 10 item per halaman
        $medicines = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched data',
            'data' => $medicines,
        ]);
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
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

        // Mengambil parameter pencarian dari 'q'
        $query = $request->input('q');

        // Mengambil data obat berdasarkan clinic dan melakukan pencarian jika parameter 'q' ada
        $medicines = $clinic->medications()->with(['pregnancyCategory'])
            ->when($query, function ($q, $query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku_code', 'like', "%{$query}%")
                    ->orWhere('price', 'like', "%{$query}%")
                    ->orWhere('expired_date', 'like', "%{$query}%");
            })
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetched data',
            'data' => $medicines,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicationRequest $request)
    {
        // Validasi data yang dikirim dari request
        $validated = $request->validated();

        try {
            // Menggunakan DB transaction untuk menjaga integritas data
            DB::transaction(function () use ($validated) {
                Medication::create($validated);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully stored.',
            ], 201);
        } catch (\Exception $e) {
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
    public function show(MedicationRecord $medicationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medication $medication)
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
            'for' => 'string|sometimes|max:255|min:3',
            'manufacture' => 'string|sometimes|max:255|min:3',
            'supplier' => 'string|sometimes|max:255|min:3',
            'sell_price' => 'numeric|sometimes',            
            'supplier_contact' => 'sometimes|string',
            'dosage' => 'sometimes|string',
        ]);

        $medication->fill($validated);

        if ($medication->isDirty()) {
            try {
                DB::transaction(function () use ($medication) {
                    $medication->save();
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

    public function addBatch(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'total_amount' => 'integer|required',
        ]);
        $medication->total_amount += $validated['total_amount'];
        $medication->batch += 1;
        try {
            DB::transaction(function () use ($medication) {
                $medication->save();
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

    public function information(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
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
        $medicines = $clinic->medications();
        $totalMedicines = $medicines->count();
        $totalStock = $medicines->sum('total_amount');
        $totalPrice = $medicines->sum('price');
        return response()->json([
            'total_medicine' => $totalMedicines,
            'total_stock' => $totalStock,
            'total_price' => $totalPrice,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medication $medication)
    {
        try {
            // Hapus pasien dari database
            $medication->delete();

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
