<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateUniqueIdHelper;
use App\Models\BoInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBoInvoiceRequest;
use App\Http\Requests\UpdateBoInvoiceRequest;

class BoInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $boInvoice = BoInvoice::with(['items'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('clinic_name', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhereHas('items', function ($qi) use ($search) {
                        $qi->where('name', 'LIKE', "%{$search}%");
                    });
                });
            })
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data'   => $boInvoice
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
    public function store(StoreBoInvoiceRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $uniqId = GenerateUniqueIdHelper::generateInvoiceId();
            $invoice = BoInvoice::create([
                'unique_id' => $uniqId,
                'invoice_date' => $validated['invoice_date'],
                'due_date'=> $validated['due_date'],
                'clinic_name'=> $validated['clinic_name'],
                'clinic_email'=> $validated['clinic_email'],
                'clinic_phone_number'=> $validated['clinic_phone_number'],
                'clinic_address'=> $validated['clinic_address'],
            ]);
            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'name'=> $item['name'],
                    'price'=> $item['price'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice Created!'
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
    public function show(BoInvoice $boInvoice)
    {
        $boInvoice->load('items');
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrived',
            'data' => $boInvoice
        ]);
    }

    public function completed(BoInvoice $boInvoice)
    {
        DB::beginTransaction();
        try {
            $boInvoice->update([
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
     * Show the form for editing the specified resource.
     */
    public function edit(BoInvoice $boInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoInvoiceRequest $request, BoInvoice $boInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoInvoice $boInvoice)
    {
        DB::beginTransaction();
        try {
            // Hapus pasien dari database
            $boInvoice->delete();
            DB::commit();
            // Mengembalikan respons sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
