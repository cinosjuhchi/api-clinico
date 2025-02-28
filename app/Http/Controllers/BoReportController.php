<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportInformationRequest;
use App\Models\Billing;
use App\Models\BoExpense;
use App\Models\BoInvoice;
use App\Models\ClinicSettlement;
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
        ], 200);
    }
    public function totalCash(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        // Ambil semua invoice dalam rentang tanggal dengan relasi items
        $cashs = BoExpense::whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'order')
            ->with(['items'])
            ->get()
            ->groupBy('expense_date');

        // Format hasilnya sesuai yang diinginkan
        $formattedCash = $cashs->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(fn ($cash) => $cash->items->sum('price')), // Total semua price dari relasi items
                'cashs' => $group->map(function ($cash) {
                    return [
                        'clinic_name' => $cash->addition['name'] ?? null,
                        'cost' => $cash->items->sum('price'), // Total price per cash
                        'status' => $cash->status,
                        'unique_id' => $cash->unique_id
                    ];
                })->values(), // Reset index array agar tidak berbentuk koleksi asosiatif
            ];
        })->values(); // Reset index array agar format JSON rapi

        // Return response dalam bentuk JSON
        return response()->json([
            'status' => 'success',
            'data' => $formattedCash,
        ], 200);
    }
    public function totalOrders(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        // Ambil semua invoice dalam rentang tanggal dengan relasi items
        $orders = BoExpense::whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'order')
            ->with(['items'])
            ->get()
            ->groupBy('expense_date');

        // Format hasilnya sesuai yang diinginkan
        $formattedOrders = $orders->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(fn ($order) => $order->items->sum('price')), // Total semua price dari relasi items
                'orders' => $group->map(function ($order) {
                    return [
                        'clinic_name' => $order->addition['ship_to_name'] ?? null,
                        'cost' => $order->items->sum('price'), // Total price per order
                        'status' => $order->status,
                        'unique_id' => $order->unique_id
                    ];
                })->values(), // Reset index array agar tidak berbentuk koleksi asosiatif
            ];
        })->values(); // Reset index array agar format JSON rapi

        // Return response dalam bentuk JSON
        return response()->json([
            'status' => 'success',
            'data' => $formattedOrders,
        ], 200);
    }

    public function settlements(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        // Ambil semua invoice dalam rentang tanggal dengan relasi items
        $settlements = ClinicSettlement::whereBetween('settlement_date', [$validated['from_date'], $validated['to_date']])
            ->with(['clinic'])
            ->get()
            ->groupBy('settlement_date');

        // Format hasilnya sesuai yang diinginkan
        $formattedSettlements = $settlements->map(function ($group, $date) {
            return [
                'settlement_date' => $date,
                'total_cost' => $group->sum(fn ($settlement) => $settlement->total_sales), // Total semua price dari relasi items
                'settlements' => $group->map(function ($settlement) {
                    return [
                        'clinico_id' => $settlement->clinico_id,
                        'clinic_name'=> $settlement->clinic->name,
                        'total_sales' => $settlement->total_sales,
                        'status' => $settlement->status,                        
                    ];
                })->values(), // Reset index array agar tidak berbentuk koleksi asosiatif
            ];
        })->values(); // Reset index array agar format JSON rapi

        // Return response dalam bentuk JSON
        return response()->json([
            'status' => 'success',
            'data' => $formattedSettlements,
        ], 200);
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
