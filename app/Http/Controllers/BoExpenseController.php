<?php

namespace App\Http\Controllers;

use App\Models\BoExpense;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBoExpenseRequest;
use App\Http\Requests\UpdateBoExpenseRequest;

class BoExpenseController extends Controller
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
    public function store(StoreBoExpenseRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();

        try {                        
            $boExpense = BoExpense::create([
                'expense_date' => $validated['expense_date'],
                'due_date' => $validated['due_date'],
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
        //
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
        //
    }
}
