<?php

namespace App\Http\Controllers;

use App\Models\BoExpense;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\GenerateUniqueIdHelper;
use App\Http\Requests\StoreBoExpenseRequest;
use App\Http\Requests\UpdateBoExpenseRequest;

class BoExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $boExpense = BoExpense::with(['items'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('unique_id', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhereHas('items', function ($qi) use ($search) {
                            $qi->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.name')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.vendor_name')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.vendor_company')) LIKE ?", ["%{$search}%"]);
                });
            })
            ->paginate(10);

        // Decode addition sebelum dikirim ke response
        $boExpense->getCollection()->transform(function ($expense) {
            $expense->addition = is_array($expense->addition) 
                ? $expense->addition 
                : json_decode($expense->addition, true);
            return $expense;
        });

        return response()->json([
            'status' => 'success',
            'data'   => $boExpense
        ], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function completed(BoExpense $boExpense)
    {
        DB::beginTransaction();
        try {
            $boExpense->update([
                'status' => 'completed'
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully confirm'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while confirm: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoExpenseRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();

        try {                                    
            $uniqId = GenerateUniqueIdHelper::generateExpenseId();
            $boExpense = BoExpense::create([
                'unique_id' => $uniqId,
                'expense_date' => $validated['expense_date'],
                'due_date' => $validated['due_date'] ?? null,
                'addition' => $validated['addition'],
                'type' => $validated['type'],                    
            ]);

            if ($validated['type'] !== 'locum' && isset($validated['items'])) {
                $boExpense->items()->createMany($validated['items']);
            }           

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully added expense'
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
    public function show(BoExpense $boExpense)
    {
        $boExpense->load('items');
        return response()->json([
            'status' => 'success',
            'message' => 'Retrieved expense is success',
            'data' => $boExpense
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoExpense $boExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoExpenseRequest $request, BoExpense $boExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoExpense $boExpense)
    {
        DB::beginTransaction();
        try {
            // Hapus pasien dari database
            $boExpense->delete();
            DB::commit();
            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
