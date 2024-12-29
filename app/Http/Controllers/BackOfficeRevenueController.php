<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BackOfficeRevenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $billing = Billing::with([
            'clinic',
            'user',
            'doctor',
            'service',
            'appointment.patient.demographics',
        ])->paginate();

        return response()->json($billing);
    }

    public function getRevenueByDate(Request $request)
    {
        // Get the from and to dates from the request
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Validate the date inputs
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        // Query the Billing model with the date range filter
        $billing = Billing::with(['clinic', 'user', 'doctor'])
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->get();

        // Return the filtered billing records as a JSON response
        return response()->json($billing);
    }

    public function totalRevenueTaxOnly(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        // Memulai query untuk mendapatkan semua tagihan yang telah dibayar
        $query = Billing::where('is_paid', true);

        // Memfilter berdasarkan bulan dan tahun pada kolom transaction_date jika diberikan
        if ($month && $year) {
            $query->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year);
        } elseif ($month) {
            $query->whereMonth('transaction_date', $month);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        // Menghitung total pendapatan dari semua tagihan
        $totalRevenue = $query->sum('total_cost');

        // Menghitung 5% dari total revenue
        $totalTaxRevenue = $totalRevenue * 0.05;

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the total tax revenue.',
            'total_tax_revenue' => $totalTaxRevenue,
            'month_year' => [
                $month,
                $year,
            ],
        ], 200);
    }
    public function totalRevenue(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        // Memulai query untuk mendapatkan semua tagihan yang telah dibayar
        $query = Billing::where('is_paid', true);

        // Memfilter berdasarkan bulan dan tahun pada kolom transaction_date jika diberikan
        if ($month && $year) {
            $query->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year);
        } elseif ($month) {
            $query->whereMonth('transaction_date', $month);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        // Menghitung total pendapatan dari semua tagihan
        $totalRevenue = $query->sum('total_cost');

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the total revenue.',
            'total_revenue' => $totalRevenue,
            'month_year' => [
                $month,
                $year,
            ],
        ], 200);
    }

    public function totalRevenueGroupedByMonth()
    {
        // Mendapatkan tahun saat ini
        $currentYear = now()->year;

        // Query untuk mendapatkan total pendapatan per bulan di tahun ini
        $revenueByMonth = Billing::where('is_paid', true)
            ->whereYear('transaction_date', $currentYear)
            ->selectRaw('MONTH(transaction_date) as month, MONTHNAME(transaction_date) as month_name, SUM(total_cost) as total_revenue')
            ->groupBy('month', 'month_name')
            ->orderBy('month') // Urutkan berdasarkan nomor bulan
            ->get()
            ->map(function ($item) {
                // Tambahkan perhitungan pajak 5% pada setiap item
                $item->total_tax = round($item->total_revenue * 0.05, 2);
                return $item;
            });

        // Mengembalikan hasil dalam format JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the total revenue grouped by month.',
            'year' => $currentYear,
            'data' => $revenueByMonth,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
