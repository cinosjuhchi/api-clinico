<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Doctor;
use App\Models\Billing;
use App\Models\Medication;
use Illuminate\Http\Request;
use App\Models\ClinicExpense;
use App\Models\ClinicInvoice;
use App\Models\MedicationRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReportInformationRequest;

// This is for Clinic only
class ClinicReportController extends Controller
{
    protected $clinicID;

    public function __construct() {
        $this->clinicID = auth()->user()->clinic->id;
    }

    // Total Sales (Cash, Transfer, & Panel)
    public function totalSales(Request $request)
    {
        $query = Billing::with('appointment.room', 'clinic.moh', 'appointment.patient')
                        ->where('clinic_id', $this->clinicID);

        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $sales = $query->get();

        $countData = count($sales);
        $totalCash = 0;
        $totalClinico = 0;
        $totalPanel = 0;
        $totalDep = 0;

        for ($i = 0; $i < $countData; $i++) {
            if ($sales[$i]->type == 'cash') {
                $totalCash += $sales[$i]->total_cost;
            } elseif ($sales[$i]->type == 'clinico') {
                $totalClinico += $sales[$i]->total_cost;
            } elseif ($sales[$i]->type == 'panel') {
                $totalPanel += $sales[$i]->total_cost;
            }
        }

        if ($request->has('group_by_doctor') && $request->group_by_doctor == 1) {
            $groupedSales = $sales->groupBy('doctor_id')->map(function ($group, $doctorId) {
                $totalCost = $group->sum('total_cost');

                return [
                    'doctor_id' => $doctorId,
                    'doctor_name' => $group->first()->doctor->name,
                    'total_cost' => round($totalCost, 2),
                    'incentive' => round($totalCost * 0.05, 2),
                    'transactions' => $group->map(function ($transaction) {
                        $transaction->incentive = round($transaction->total_cost * 0.05, 2);
                        return $transaction;
                    })->values()
                ];
            })->values();

            return response()->json([
                "status" => "success",
                "message" => "Total Sales Grouped by Doctor",
                "total" => [
                    "sales_count" => $countData,
                    "cash" => round($totalCash, 2),
                    "clinico" => round($totalClinico, 2),
                    "panel" => round($totalPanel, 2),
                ],
                "data" => $groupedSales
            ]);
        }



        return response()->json([
            "status" => "success",
            "message" => "Total Sales",
            "total" => [
                "sales_count" => $countData,
                "cash" => $totalCash,
                "clinico" => $totalClinico,
                "panel" => $totalPanel,
            ],
            "data" => $sales
        ]);
    }

    public function invoices(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $invoices = ClinicInvoice::whereBetween('invoice_date', [$validated['from_date'], $validated['to_date']])
            ->with(['items'])
            ->get()
            ->groupBy('invoice_date');

        $formattedInvoices = $invoices->map(function ($group, $date) {
            return [
                'invoice_date' => $date,
                'total_cost' => $group->sum(fn ($invoice) => $invoice->items->sum('price')),
                'invoices' => $group->map(function ($invoice) {
                    return [
                        'clinic_name' => $invoice->clinic_name,
                        'cost' => $invoice->items->sum('price'),
                        'status' => $invoice->status,
                        'unique_id' => $invoice->unique_id
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $formattedInvoices,
        ], 200);
    }

    public function totalCash(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $cashs = Auth::user()->clinic->expenses()->whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'cash')
            ->with(['items'])
            ->get()
            ->groupBy('expense_date');

        $formattedCash = $cashs->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(fn ($cash) => $cash->items->sum('price')),
                'cashs' => $group->map(function ($cash) {
                    return [
                        'clinic_name' => $cash->addition['name'] ?? null,
                        'cost' => $cash->items->sum('price'),
                        'status' => $cash->status,
                        'unique_id' => $cash->unique_id
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $formattedCash,
        ], 200);
    }

    public function totalOrders(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $orders = Auth::user()->clinic->expenses()->whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'order')
            ->with(['items'])
            ->get()
            ->groupBy('expense_date');

        $formattedOrders = $orders->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(fn ($order) => $order->items->sum('price')),
                'orders' => $group->map(function ($order) {
                    return [
                        'clinic_name' => $order->addition['ship_to_name'] ?? null,
                        'cost' => $order->items->sum('price'),
                        'status' => $order->status,
                        'unique_id' => $order->unique_id
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $formattedOrders,
        ], 200);
    }

    public function totalVouchers(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $vouchers = Auth::user()->clinic->expenses()->whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'voucher')
            ->with(['items'])
            ->get()
            ->groupBy('expense_date');

        $formattedVouchers = $vouchers->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(fn ($voucher) => $voucher->items->sum('price')),
                'vouchers' => $group->map(function ($voucher) {
                    return [
                        'clinic_name' => $voucher->addition['name'] ?? null,
                        'cost' => $voucher->items->sum('price'),
                        'status' => $voucher->status,
                        'unique_id' => $voucher->unique_id
                    ];
                })->values(),
            ];
        })->values();


        return response()->json([
            'status' => 'success',
            'data' => $formattedVouchers,
        ], 200);
    }


    public function totalLocums(ReportInformationRequest $request)
    {
        $validated = $request->validated();

        $locums = Auth::user()->clinic->expenses()->whereBetween('expense_date', [$validated['from_date'], $validated['to_date']])
            ->where('type', 'locum')
            ->get()
            ->groupBy('expense_date');

        $formattedLocums = $locums->map(function ($group, $date) {
            return [
                'expense_date' => $date,
                'total_cost' => $group->sum(function ($locum) {
                    $addition = $locum->addition;


                    if (!isset($addition['items']) || !is_array($addition['items'])) {
                        return 0;
                    }

                    return collect($addition['items'])->sum(function ($item) {
                        return ($item['locum_fee'] ?? 0) +
                            ($item['procedure_fee'] ?? 0) +
                            ($item['patient_fee'] ?? 0) +
                            ($item['night_slot_fee'] ?? 0);
                    });
                }),
                'locums' => $group->map(function ($locum) {
                    return [
                        'clinic_name' => data_get($locum->addition, 'name'),
                        'cost' => collect(data_get($locum->addition, 'items', []))->sum(function ($item) {
                            return data_get($item, 'locum_fee', 0) +
                                   data_get($item, 'procedure_fee', 0) +
                                   data_get($item, 'patient_fee', 0) +
                                   data_get($item, 'night_slot_fee', 0);
                        }),
                        'status' => $locum->status,
                        'unique_id' => $locum->unique_id
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $formattedLocums,
        ], 200);
    }

    public function averageChargePerPatient()
    {
        $billings = DB::table('billings')
            ->where('clinic_id', $this->clinicID)
            ->leftJoin('patients', 'billings.patient_id', '=', 'patients.id')
            ->select(
                'billings.patient_id',
                'patients.name', // patient name
                DB::raw('SUM(total_cost) as total_seluruh'),
                DB::raw("SUM(CASE WHEN type = 'cash' THEN total_cost ELSE 0 END) as total_cash"),
                DB::raw("SUM(CASE WHEN type = 'clinico' THEN total_cost ELSE 0 END) as total_clinico"),
                DB::raw("SUM(CASE WHEN type = 'panel' THEN total_cost ELSE 0 END) as total_panel")
            )
            ->groupBy('patient_id')
            ->get();

        // Menghitung total pasien
        $totalPatients = $billings->count();

        // Menghitung total keseluruhan dari masing-masing kategori
        $totalSeluruh = $billings->sum('total_seluruh');
        $totalCash = $billings->sum('total_cash');
        $totalClinico = $billings->sum('total_clinico');
        $totalPanel = $billings->sum('total_panel');

        // Menghitung rata-rata per pasien
        $averageSeluruh = $totalPatients > 0 ? $totalSeluruh / $totalPatients : 0;
        $averageCash = $totalPatients > 0 ? $totalCash / $totalPatients : 0;
        $averageClinico = $totalPatients > 0 ? $totalClinico / $totalPatients : 0;
        $averagePanel = $totalPatients > 0 ? $totalPanel / $totalPatients : 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Average Charge Per Patient',
            'total_patients' => $totalPatients,
            'totals' => [
                'total_seluruh' => $totalSeluruh,
                'total_cash' => $totalCash,
                'total_clinico' => $totalClinico,
                'total_panel' => $totalPanel,
            ],
            'averages' => [
                'average_seluruh' => $averageSeluruh,
                'average_cash' => $averageCash,
                'average_clinico' => $averageClinico,
                'average_panel' => $averagePanel,
            ],
            'data' => $billings
        ]);
    }

    public function locum()
    {
        $doctor = Auth::user()->clinic->doctors()->with([
            'category',
            'schedules.room'
        ])
            ->whereHas('category', function ($query) {
                $query->where('name', 'locum');
            })
            ->get();

        return response()->json([
            "status" => "success",
            "message" => "Get Locum",
            "data" => $doctor
        ]);
    }

    public function commonlyPrescribeMedicine()
    {
        $medicine = Auth::user()->clinic->medications()->with('records')
                            ->withCount('records')
                            ->withSum('records', 'qty')
                            ->orderBy('records_count', 'desc')
                            ->paginate(20);

        $totalStock = $medicine->sum('total_amount');

        return response()->json([
            'status' => 'success',
            'message' => 'Commonly Prescribe Medicine',
            'total_stock' => $totalStock,
            'data' => $medicine
        ]);
    }
}
