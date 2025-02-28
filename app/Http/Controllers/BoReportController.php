<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;

class BoReportController extends Controller
{
    public function totalSales(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|before:to_date',
            'to_date' => 'required|date|after:from_date'
        ]);

        
        
        return response()->json([
            'status' => 'success',
            'data'   => 'hello'
        ], 200);
    }

}
