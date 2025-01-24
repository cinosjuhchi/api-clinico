<?php
namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Clinic;
use App\Models\ClinicSettlement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicSettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClinicSettlement::with(['clinic.financial']);

        // Jika ada parameter pencarian, tambahkan filter
        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('clinico_id', 'like', '%' . $searchTerm . '%');
        }

        // Ambil data dengan paginasi
        $data = $query->paginate();

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'  => 'required|date',          // Tanggal yang dipilih oleh user
            'count' => 'required|integer|min:1', // Jumlah clinic yang akan diproses
        ]);

        $selectedDate = Carbon::parse($validated['date'])->startOfDay();
        $clinicCount  = $validated['count'];

        // Ambil clinics yang belum memiliki settlement pada tanggal yang dipilih
        $clinicsWithoutSettlement = Clinic::whereDoesntHave('settlements', function ($query) use ($selectedDate) {
            $query->whereDate('settlement_date', $selectedDate);
        })
            ->orderBy('id')       // Urutkan sesuai urutan database
            ->limit($clinicCount) // Batasi jumlah clinic yang akan diproses
            ->get();

        if ($clinicsWithoutSettlement->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Semua clinic sudah memiliki settlement pada tanggal tersebut.',
            ], 400);
        }

        // Ambil nomor urut terakhir dari clinico_id
        $lastSettlement = ClinicSettlement::latest('id')->first();
        $lastNumber     = $lastSettlement ? intval(substr($lastSettlement->clinico_id, 2)) : 0;

        // Generate laporan untuk setiap clinic
        $settlements = [];
        foreach ($clinicsWithoutSettlement as $clinic) {
            // Hitung total pendapatan berdasarkan type (cash, panel, clinico)
            $billSummary = Billing::where('clinic_id', $clinic->id)
                ->whereDate('created_at', $selectedDate)
                ->selectRaw('
                SUM(CASE WHEN type = "cash" THEN amount ELSE 0 END) AS cash_total,
                SUM(CASE WHEN type = "panel" THEN amount ELSE 0 END) AS panel_total,
                SUM(CASE WHEN type = "clinico" THEN amount ELSE 0 END) AS clinico_total
            ')
                ->first();

            $cashTotal    = $billSummary->cash_total ?? 0;
            $panelTotal   = $billSummary->panel_total ?? 0;
            $clinicoTotal = $billSummary->clinico_total ?? 0;

            // Hitung total sales dan total tax
            $totalSales    = $cashTotal + $panelTotal + $clinicoTotal;
            $totalTax      = $totalSales * 0.05;
            $netSales      = $totalSales - $totalTax;
            $netSettlement = $clinicoTotal - $totalTax;

            // Generate clinico_id
            $lastNumber++;
            $clinicoId = 'KK' . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);

            // Simpan data settlement ke dalam array
            $settlements[] = [
                'clinic_id'           => $clinic->id,
                'clinico_id'          => $clinicoId,
                'settlement_date'     => $selectedDate,
                'total_sales_cash'    => $cashTotal,
                'total_sales_panel'   => $panelTotal,
                'total_sales_clinico' => $clinicoTotal,
                'total_sales'         => $totalSales,
                'fee'                 => $totalTax,
                'nett_sales'          => $netSales,
                'status'              => 'pending',
                'nett_settlement'     => $netSettlement,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // Simpan data settlement ke database
        ClinicSettlement::insert($settlements);

        return response()->json([
            'status'  => 'success',
            'message' => 'Settlement berhasil dibuat.',
            'data'    => $settlements,
        ]);
    }

    public function completed(ClinicSettlement $clinicSettlement)
    {
        DB::beginTransaction();
        try {
            $clinicSettlement->update([
                'status' => 'completed',
            ]);
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'completed!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicSettlement $clinicSettlement)
    {
        $clinicSettlement->load(['clinic.financial']);
        return response()->json([
            'status' => 'success',
            'data'   => $clinicSettlement,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Billing $billing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicSettlement $clinicSettlement)
    {
        DB::beginTransaction();
        try {
            $clinicSettlement->delete();
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'deleted!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }
}
