<?php

namespace App\Http\Controllers\Api\V1\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $clinic = Clinic::with('doctors')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Success to get clinic data.',
            'data' => $clinic
        ], 200);
    }
}
