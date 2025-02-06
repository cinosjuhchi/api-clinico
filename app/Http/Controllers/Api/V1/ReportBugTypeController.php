<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReportBugType;
use Illuminate\Http\Request;

class ReportBugTypeController extends Controller
{
    public function index()
    {
        $bugTypes = ReportBugType::get();
        return response()->json([
            "status" => "success",
            "message" => "Bug types retrieved successfully",
            "data" => $bugTypes,
        ]);
    }
}
