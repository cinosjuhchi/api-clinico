<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientRequest;
use App\Models\Billing;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function patients()
    {
        $doctor = Auth::user()->doctor;
        $collection = $doctor->load([
            'bills.appointment.patient.demographics',
            'bills.appointment.doctor',
            'bills.appointment.service'
        ]);

        $totalCashSales = $collection->bills()->where('type', 'cash')->sum('total_cost');
        $totalPanelSales = $collection->bills()->where('type', 'panel')->sum('total_cost');
        $totalDailySales = $collection->bills()->where('transaction_date', date('Y-m-d'))->sum('total_cost');

        return response()->json([
            "status" => "success",
            "data" => [
                "total_cash_sales" => $totalCashSales,
                "total_panel_sales" => $totalPanelSales,
                "total_daily_sales" => $totalDailySales,
                "data" => $collection,
            ]
        ]);
    }

    public function updatePatient(UpdatePatientRequest $request, $billId)
    {
        $bill = Billing::find($billId);

        if (!$bill) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bill not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $bill->update([
                'total_cost' => $request->amount,
            ]);

            $appointment = $bill->appointment;
            $appointment->update([
                'visit_number' => $request->visit_number,
                'type' => $request->type,
                'appointment_date' => $request->appointment_date,
                'clinic_service_id' => $request->service_id,
                'doctor_id' => $request->doctor_id,
            ]);

            $patient = $appointment->patient;
            $patient->update([
                'name' => $request->name,
            ]);

            $demographics = $patient->demographics;
            $demographics->update([
                'nric' => $request->nric,
                'mrn' => $request->mrn,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Patient updated successfully',
                'data' => $bill
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor, Request $request)
{
    $day = $request->input('day');

    $doctor->load([
        'category',
        'clinic',
        'doctorSchedules' => function ($query) use ($day) {
            if ($day) {
                $query->where('day', $day)->first();
            }
        }
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Doctor retrieved successfully',
        'data' => $doctor
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }
}
