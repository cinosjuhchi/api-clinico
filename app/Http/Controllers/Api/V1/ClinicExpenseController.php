<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\GenerateUniqueIdHelper;
use Illuminate\Http\Request;
use App\Models\ClinicExpense;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClinicExpenseRequest;
use Illuminate\Support\Facades\DB;

class ClinicExpenseController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        $clinicExpense = ClinicExpense::with(['items', 'clinic'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('unique_id', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhereHas('items', function ($qi) use ($search) {
                            $qi->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.name')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.vendor_name')) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(addition, '$.vendor_company')) LIKE ?", ["%{$search}%"]);
                });
            })
            ->paginate(10);

        $totals = [
            'total_cash' => 0,
            'total_payment_voucher' => 0,
            'total_purchase_order' => 0,
            'total_locum_payment' => 0,
        ];

        $clinicExpense->getCollection()->transform(function ($expense) use (&$totals) {
            $expense->addition = is_array($expense->addition)
                ? $expense->addition
                : json_decode($expense->addition, true);

            if ($expense->type === 'locum') {
                $totalPrice = collect($expense->addition['items'] ?? [])->sum(function ($item) {
                    $unit = isset($item['unit']) ? (float) $item['unit'] : 0;
                    $fees = [
                        'locum_fee' => isset($item['locum_fee']) ? (float) $item['locum_fee'] : 0,
                        'procedure_fee' => isset($item['procedure_fee']) ? (float) $item['procedure_fee'] : 0,
                        'patient_fee' => isset($item['patient_fee']) ? (float) $item['patient_fee'] : 0,
                        'night_slot_fee' => isset($item['night_slot_fee']) ? (float) $item['night_slot_fee'] : 0,
                    ];
                    return $unit * array_sum($fees);
                });
            } else {
                $totalPrice = collect($expense->items ?? [])->sum(function ($item) {
                    return isset($item['price']) ? (float) $item['price'] : 0;
                });
            }


            switch ($expense->type) {
                case 'cash':
                    $totals['total_cash'] += $totalPrice;
                    break;
                case 'voucher':
                    $totals['total_payment_voucher'] += $totalPrice;
                    break;
                case 'order':
                    $totals['total_purchase_order'] += $totalPrice;
                    break;
                case 'locum':
                    $totals['total_locum_payment'] += $totalPrice;
                    break;
            }

            return $expense;
        });

        return response()->json([
            'status' => 'success',
            'total_cash' => $totals['total_cash'],
            'total_payment_voucher' => $totals['total_payment_voucher'],
            'total_purchase_order' => $totals['total_purchase_order'],
            'total_locum_payment' => $totals['total_locum_payment'],
            'data'   => $clinicExpense
        ], 200);
    }


    public function show(ClinicExpense $clinicExpense)
    {
        $clinicExpense->load(['items', 'clinic']);
        return response()->json([
            'status' => 'success',
            'message' => 'Retrieved expense is success',
            'data' => $clinicExpense
        ], 200);
    }

    public function store(StoreClinicExpenseRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            switch ($validated['type']) {
                case 'cash':
                    $prefix = 'CV';
                    break;
                case 'voucher':
                    $prefix = 'PV';
                    break;
                case 'order':
                    $prefix = 'OV';
                    break;
                case 'locum':
                    $prefix = 'LV';
                    break;
                default:
                    $prefix = 'EXP';
                    break;
            }

            $uniqId = GenerateUniqueIdHelper::generateExpenseId($prefix, 'clinic');
            $clinicExpense = ClinicExpense::create([
                'unique_id' => $uniqId,
                'expense_date' => $validated['expense_date'],
                'due_date' => $validated['due_date'] ?? null,
                'addition' => $validated['addition'],
                'type' => $validated['type'],
                'clinic_id' => auth()->user()->clinic->id
            ]);

            if ($validated['type'] !== 'locum' && isset($validated['items'])) {
                $clinicExpense->items()->createMany($validated['items']);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully added expense'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function completed(ClinicExpense $clinicExpense)
    {
        DB::beginTransaction();
        try {
            $clinicExpense->update([
                'status' => 'completed'
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully confirm'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while confirm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ClinicExpense $clinicExpense)
    {
        DB::beginTransaction();
        try {
            $clinicExpense->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the procedure: ' . $e->getMessage(),
            ], 500);
        }
    }
}
