<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;

class BoReportController extends Controller
{
    public function totalSales(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date'
        ]);

        $totalSales = Billing::whereBetween('transaction_date', [$validated['from_date'], $validated['to_date']])
            ->where('is_paid', true)
            ->with(['clinic', 'patient.demographics'])
            ->selectRaw('transaction_date, SUM(total_cost) as total_sales, COUNT(DISTINCT patient_id) as total_patient')
            ->groupBy('transaction_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $totalSales
        ], 200);
    }

}
