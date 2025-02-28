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

        // Ambil semua invoice dalam rentang tanggal dengan relasi items
        $invoices = BoInvoice::whereBetween('invoice_date', [$validated['from_date'], $validated['to_date']])
            ->with(['items'])
            ->get()
            ->groupBy('invoice_date');

        // Format hasilnya sesuai yang diinginkan
        $formattedInvoices = $invoices->map(function ($group, $date) {
            return [
                'invoice_date' => $date,
                'total_cost' => $group->sum(fn ($invoice) => $invoice->items->sum('price')), // Total semua price dari relasi items
                'invoices' => $group->map(function ($invoice) {
                    return [
                        'clinic_name' => $invoice->clinic_name,
                        'cost' => $invoice->items->sum('price'), // Total price per invoice
                        'status' => $invoice->status,
                        'unique_id' => $invoice->unique_id
                    ];
                })->values(), // Reset index array agar tidak berbentuk koleksi asosiatif
            ];
        })->values(); // Reset index array agar format JSON rapi

        // Return response dalam bentuk JSON
        return response()->json([
            'status' => 'success',
            'data' => $formattedInvoices,
        ]);
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
