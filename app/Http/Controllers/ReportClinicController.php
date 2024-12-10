<?php

namespace App\Http\Controllers;

use App\Helpers\GetAdminHelper;
use App\Http\Requests\ApprovedReportRequest;
use App\Http\Requests\StoreReportClinicRequest;
use App\Http\Requests\UpdateReportClinicRequest;
use App\Models\ReportClinic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ReportClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    public function getPendingReport(Request $request)
    {
        // Ambil parameter pencarian dari request
        $title = $request->query('title');
        $date = $request->query('date');

        // Query dasar untuk laporan dengan status 'pending'
        $query = ReportClinic::where('status', 'pending');

        // Tambahkan kondisi pencarian berdasarkan judul jika parameter 'title' diberikan
        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // Tambahkan kondisi pencarian berdasarkan tanggal jika parameter 'date' diberikan
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Paginate hasil query
        $data = $query->paginate(5);

        return response()->json($data);
    }
    public function getCompleteReport(Request $request)
    {
        // Ambil parameter pencarian dari request
        $title = $request->query('title');
        $date = $request->query('date');

        // Query dasar untuk laporan dengan status 'pending'
        $query = ReportClinic::where('status', 'resolve');

        // Tambahkan kondisi pencarian berdasarkan judul jika parameter 'title' diberikan
        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // Tambahkan kondisi pencarian berdasarkan tanggal jika parameter 'date' diberikan
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Paginate hasil query
        $data = $query->paginate(5);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function approved(ApprovedReportRequest $request, ReportClinic $reportClinic)
    {
        $validated = $request->validated();
        $user = GetAdminHelper::getAdminData();
        if (!$user) {
            return response()->json([
                'status' => 'forbidden',
                'message' => "You can't access this.",
            ], 403);
        }

        try {
            DB::beginTransaction();

            $reportClinic->update([
                'status' => $validated['status'],
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully changed the status.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Error something wrong happened',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportClinicRequest $request)
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();
            ReportClinic::create([
                'reported_by' => $validated['reported_by'],
                'clinic_id' => $validated['clinic_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully report the clinic.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Error something wrong happened!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportClinic $reportClinic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportClinic $reportClinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportClinicRequest $request, ReportClinic $reportClinic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportClinic $reportClinic)
    {
        //
    }
}
