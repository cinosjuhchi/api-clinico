<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitorRequest;
use App\Http\Requests\UpdateVisitorRequest;
use App\Models\Visitor;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getTotalViewPage()
    {
        // Menghitung total visit berdasarkan visit_count
        $totalVisits = Visitor::sum('visit_count'); // Ganti Visit dengan nama model Anda

        return response()->json([
            'message' => 'Total visits calculated successfully.',
            'total_visits' => $totalVisits,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVisitorRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $ipAddress = $request->ip();

            // Simpan data ke dalam database
            $visit = new Visitor(); // Ganti `Visit` dengan nama model Anda
            $visit->ip_address = $ipAddress;
            $visit->user_agent = $validatedData['user_agent'] ?? $request->header('User-Agent'); // Ambil user agent dari header jika tidak disediakan
            $visit->browser = $validatedData['browser'] ?? null;
            $visit->browser_version = $validatedData['browser_version'] ?? null;
            $visit->device = $validatedData['device'] ?? null;
            $visit->operating_system = $validatedData['operating_system'] ?? null;
            $visit->page_url = $validatedData['page_url'];
            $visit->referrer_url = $validatedData['referrer_url'] ?? null;
            $visit->country = $validatedData['country'] ?? null;
            $visit->city = $validatedData['city'] ?? null;
            $visit->visit_count = 1; // Default value
            $visit->last_visit = now(); // Current timestamp
            $visit->save();
            DB::commit();

            // Kembalikan respon JSON
            return response()->json([
                'message' => 'Visit data stored successfully.',
                'data' => $visit,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
            //throw $th;
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Visitor $visitor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Visitor $visitor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVisitorRequest $request, Visitor $visitor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visitor $visitor)
    {
        //
    }
}
