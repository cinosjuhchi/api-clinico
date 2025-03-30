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
        DB::beginTransaction();
        try {                        
                $total_cost = $validated['sell_price'] * $validated['total_amount'];
                $validated['total_cost'] = number_format($total_cost, 2, '.', '');
                $medication = Medication::create([
                    'name' => $validated['name'],
                    'price' => $validated['price'],
                    'brand' => $validated['brand'],
                    'pregnancy_category_id' => $validated['pregnancy_category_id'],
                    'sku_code' => $validated['sku_code'],
                    'paediatric_dose' => $validated['paediatric_dose'],
                    'unit' => $validated['unit'],
                    'batch' => $validated['batch'],
                    'expired_date' => $validated['expired_date'],
                    'total_amount' => $validated['total_amount'],
                    'manufacture' => $validated['manufacture'],
                    'for' => $validated['for'],
                    'supplier' => $validated['supplier'],
                    'clinic_id' => $validated['clinic_id'],
                    'sell_price' => $validated['sell_price'],
                    'suplier_contact' => $validated['suplier_contact'],
                    'dosage' => $validated['dosage'],
                ]);
                if(!empty($validated['allergies']))
                {
                    foreach ($validated['allergies'] as $item) {
                        $medication->allergies()->create([
                            'name' => $validated['name'],
                            'reaction' => $validated['reaction'],
                        ]);                        
                    }
                }            
            DB::commit();
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
            'name' => 'sometimes|required|string|max:255|min:3',
            'price' => 'sometimes|required|numeric',
            'brand' => 'sometimes|required|string|max:255|min:3',
            'pregnancy_category_id' => 'sometimes|required|exists:pregnancy_categories,id',
            'sku_code' => 'sometimes|required|string|max:255|min:5',
            'paediatric_dose' => 'sometimes|required|integer',
            'unit' => 'sometimes|required|string|max:255',
            'expired_date' => 'sometimes|required|date',
            'for' => 'sometimes|required|string|max:255|min:3',
            'manufacture' => 'sometimes|required|string|max:255|min:3',
            'supplier' => 'sometimes|required|string|max:255|min:3',
            'sell_price' => 'sometimes|required|numeric',
            'supplier_contact' => 'nullable|string',
            'dosage' => 'nullable|string',
            'allergies' => 'nullable|array',
            'allergies.*.name' => 'required|string',
            'allergies.*.reaction' => 'required|string',
        ]);

        // Hitung total_cost jika sell_price diberikan
        if (isset($validated['sell_price'])) {
            $total_cost = $validated['sell_price'] * $medication->total_amount;
            $validated['total_cost'] = number_format($total_cost, 2, '.', '');
        }

        return DB::transaction(function () use ($medication, $validated) {
            // Update data medication
            $medication->update($validated);

            // Jika ada alergi baru, perbarui relasi
            if (!empty($validated['allergies'])) {
                $medication->allergies()->delete(); // Hapus alergi lama
                foreach ($validated['allergies'] as $allergy) {
                    $medication->allergies()->create([
                        'name' => $allergy['name'],
                        'reaction' => $allergy['reaction'],
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Update successfully!',
            ], 200);
        });
    }


    public function addBatch(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'total_amount' => 'integer|required',
        ]);
        $medication->total_amount += $validated['total_amount'];
        $medication->batch += 1;

        $total_cost = $medication->sell_price * $medication->total_amount;
        $medication->total_cost = number_format($total_cost, 2, '.', '');
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
        $totalPrice = $medicines->sum('total_cost');
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
