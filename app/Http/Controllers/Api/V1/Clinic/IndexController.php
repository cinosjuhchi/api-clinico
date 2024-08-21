<?php

namespace App\Http\Controllers\Api\V1\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $user = Clinic::all();
        return response()->json(['clinics' => $user], 200);
    }
}
