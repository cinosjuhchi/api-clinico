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
            'doctor'
        ])->paginate();
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
