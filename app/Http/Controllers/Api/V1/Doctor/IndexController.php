<?php

namespace App\Http\Controllers\Api\V1\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $user = Doctor::all();
        return response()->json(['doctors' => $user], 200);
    }

    public function show($id)
    {
        $user = Doctor::find($id);
        return response()->json(['data' => $user], 200);
    }
}
