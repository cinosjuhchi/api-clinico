<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\ClinicInvoiceItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ClinicInvoiceItemController extends Controller
{
    public function destroy(ClinicInvoiceItem $clinicInvoiceItem)
    {
        DB::beginTransaction();
        try {
            $clinicInvoiceItem->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
