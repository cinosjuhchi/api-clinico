<?php

namespace App\Http\Controllers;

use App\Imports\DiagnosesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportDiagnosis extends Controller
{
    public function importExcel(Request $request)
    {
        Excel::import(new DiagnosesImport, $request->file('excel-file'));
        return redirect()->back()->with('success','');
    }
}
