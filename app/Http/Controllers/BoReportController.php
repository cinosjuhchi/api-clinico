<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportInformationRequest;
use App\Models\Billing;
use App\Models\BoInvoice;
use Illuminate\Http\Request;

class BoReportController extends Controller
{

    public function invoices(ReportInformationRequest $request)
    {
        $validated = $request->validated();
        $invoices = BoInvoice::whereBetween('invoice_date', $validated['from_date'], $validated['to_date'])
        ->with(['items'])
        ->get()
        ->groupBy('invoice_date');

    }

    public function totalSales(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $transactions = Billing::whereBetween('transaction_date', [$validated['from_date'], $validated['to_date']])
            ->where('is_paid', true)
            ->with(['clinic', 'patient.demographics'])
            ->get()
            ->groupBy('transaction_date')
            ->map(function ($items, $date) {
                return [
                    'transaction_date' => $date,
                    'total_sales' => $items->sum('total_cost'),
                    'total_patient' => $items->unique('patient_id')->count(),
                    'transaction' => $items->map(function ($item) {
                        return [
                            'clinic' => $item->clinic,
                            'patient' => $item->patient
                        ];
                    })->values()
                ];
            })
            ->values(); // Mengembalikan array numerik

        return response()->json([
            'status' => 'success',
            'data'   => $transactions
        ], 200);
    }


}
