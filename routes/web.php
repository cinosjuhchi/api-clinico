<?php

use Minishlink\WebPush\VAPID;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportDiagnosis;
use App\Http\Controllers\DiagnosisController;

Route::get('/', function () {
    // var_dump(VAPID::createVapidKeys()); // store the keys afterwards

});

Route::get('/check-timezone', function () {
    return response()->json([
        'timezone' => config('app.timezone'),
        'now' => now()->toDateTimeString(), // Menggunakan waktu default dari Laravel (APP_TIMEZONE)
        'utc_now' => now()->setTimezone('UTC')->toDateTimeString(), // Konversi ke UTC
    ]);
});

Route::get('/import-diagnosis', function () {
    return view('import-excel');
});

Route::post('/import-excel', [ImportDiagnosis::class, 'importExcel'])->name('import');
