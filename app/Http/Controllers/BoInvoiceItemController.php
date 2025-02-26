<?php

namespace App\Http\Controllers;

use App\Models\BoInvoiceItem;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBoInvoiceItemRequest;
use App\Http\Requests\UpdateBoInvoiceItemRequest;

class BoInvoiceItemController extends Controller
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
    public function store(StoreBoInvoiceItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BoInvoiceItem $boInvoiceItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoInvoiceItem $boInvoiceItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoInvoiceItemRequest $request, BoInvoiceItem $boInvoiceItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoInvoiceItem $boInvoiceItem)
    {
        DB::beginTransaction();
        try {
            // Hapus pasien dari database
            $boInvoiceItem->delete();
            DB::commit();
            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully.',
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
