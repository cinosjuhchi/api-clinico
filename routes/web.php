<?php

use Minishlink\WebPush\VAPID;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportDiagnosis;
use App\Http\Controllers\DiagnosisController;

Route::get('/', function () {
    // var_dump(VAPID::createVapidKeys()); // store the keys afterwards

});

Route::get('/import-diagnosis', function () {
    return view('import-excel');
});

Route::post('/import-excel', [ImportDiagnosis::class, 'importExcel'])->name('import');
