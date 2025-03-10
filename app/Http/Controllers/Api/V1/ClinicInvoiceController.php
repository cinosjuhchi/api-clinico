<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\ClinicInvoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\GenerateUniqueIdHelper;
use App\Http\Requests\StoreClinicInvoiceRequest;

class ClinicInvoiceController extends Controller
{
    protected $clinicID;

    public function __construct()
    {
        $this->clinicID = auth()->user()->clinic->id;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $clinicInvoice = Auth::user()->clinic->invoices()->with(['items'])
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
            'data'   => $clinicInvoice
        ], 200);
    }

    public function store(StoreClinicInvoiceRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $uniqId = GenerateUniqueIdHelper::generateInvoiceId('INC', 'clinic');
            $invoice = ClinicInvoice::create([
                'unique_id' => $uniqId,
                'invoice_date' => $validated['invoice_date'],
                'due_date'=> $validated['due_date'],
                'clinic_name'=> $validated['clinic_name'],
                'clinic_address'=> $validated['clinic_address'],
                'clinic_id' => $this->clinicID
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

    public function show(ClinicInvoice $clinicInvoice)
    {
        $clinicInvoice->load('items');
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrived',
            'data' => $clinicInvoice
        ]);
    }

    public function completed(ClinicInvoice $clinicInvoice)
    {
        DB::beginTransaction();
        try {
            $clinicInvoice->update([
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

    public function destroy(ClinicInvoice $clinicInvoice)
    {
        DB::beginTransaction();
        try {
            $clinicInvoice->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
