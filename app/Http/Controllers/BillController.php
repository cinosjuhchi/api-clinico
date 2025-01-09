<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillStoreRequest;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q');

        $bills = $user->bills()
            ->with(['appointment.clinic'])
            ->when($query, function ($q) use ($query) {
                $q->where('transaction_date', 'like', "%{$query}%")
                    ->orWhere('is_paid', 'like', "%{$query}%")
                    ->orWhere(function ($queryBuilder) use ($query) {
                        if (is_numeric($query)) {
                            $queryBuilder->where('total_cost', 'like', "%" . ($query * 1000) . "%");
                        }
                    })
                    ->orWhereHas('appointment.clinic', function ($queryBuilder) use ($query) {
                        $queryBuilder->where('name', 'like', "%{$query}%");
                    });
            })
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $bills,
        ]);
    }

    public function clinicRevenue(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $revenue = $clinic->bills()
            ->with(['user', 'appointment'])
            ->where('is_paid', true)
            ->paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the data.',
            'data' => $revenue,
        ], 200);
    }

    public function clinicTotalRevenue(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $month = $request->input('month');
        $year = $request->input('year');

        // Memfilter berdasarkan bulan dan tahun pada kolom transaction_date jika diberikan
        $query = $clinic->bills()->where('is_paid', true);

        if ($month && $year) {
            $query->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year);
        } elseif ($month) {
            $query->whereMonth('transaction_date', $month);
        } elseif ($year) {
            $query->whereYear('transaction_date', $year);
        }

        $totalRevenue = $query->sum('total_cost');

        // Mengurangi 5% dari total revenue
        $adjustedRevenue = $totalRevenue * 0.95;

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the total revenue.',
            'total_revenue' => $adjustedRevenue,
        ], 200);
    }
    public function clinicDailyTotalRevenue(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }

        $date = $request->input('date');

        // Memfilter berdasarkan tanggal pada kolom transaction_date jika diberikan
        $query = $clinic->bills()->where('is_paid', true);

        if ($date) {
            $query->whereDate('transaction_date', $date);
        }

        $totalRevenue = $query->sum('total_cost');

        // Mengurangi 5% dari total revenue
        $adjustedRevenue = $totalRevenue * 0.95;

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch the daily total revenue.',
            'total_revenue' => $adjustedRevenue,
        ], 200);
    }
    public function clinicTotalRevenueByDoctor(Request $request)
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found.',
            ], 404);
        }
        $query = $clinic->bills()
            ->where('is_paid', true)
            ->join('doctors', 'billings.doctor_id', '=', 'doctors.id')
            ->leftJoin('categories', 'doctors.category_id', '=', 'categories.id')
            ->leftJoin('employees', 'doctors.employee_id', '=', 'employees.id')
            ->select(
                'doctors.id',
                'doctors.name as doctor_name',
                'categories.id as category_id',
                'categories.name as category_name',
                'employees.image_profile as image',
                DB::raw('SUM(billings.total_cost) as total_revenue'),
                DB::raw('COUNT(billings.id) as total_patients')
            )
            ->groupBy('doctors.id', 'doctors.name', 'categories.id', 'categories.name', 'employees.image_profile');

        $doctorsRevenue = $query->get()->map(function ($item) {
            return [
                'doctor_id' => $item->id,
                'doctor_name' => $item->doctor_name,
                'total_patients' => $item->total_patients,
                'category_name' => $item->category_name,
                'total_revenue' => $item->total_revenue,
                'image' => $item->image,
                'adjusted_revenue' => $item->total_revenue * 0.95, // Pengurangan 5%
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Success to fetch doctors revenue.',
            'data' => $doctorsRevenue,
        ], 200);
    }

    public function callback(Request $request)
    {
        $billId = $request->input('id');
        $paid = $request->input('paid');
        $signature = $request->header('X-Signature');
        $data = $request->all();
        $xSignature = $data['x_signature'] ?? null;

        // Hapus x_signature dari data untuk validasi
        unset($data['x_signature']);

        // Validasi signature
        if (!$this->validateSignature($data, $xSignature)) {
            Log::error('Invalid Billplz signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $billing = Billing::where('billz_id', $billId)->first();
        $billing->update([
            'is_paid' => true,
        ]);

        $appointment = $billing->appointment;

        $appointment->update([
            'status' => 'completed',
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    private function validateSignature(array $data, ?string $xSignature): bool
    {
        if (!$xSignature) {
            return false;
        }

        // 1. Buat array source strings
        $sourceStrings = [];
        foreach ($data as $key => $value) {
            // Convert empty values to empty string
            if ($value === null || $value === '') {
                $value = '';
            }
            $sourceStrings[] = $key . $value;
        }

        // 2. Sort array secara case-insensitive
        sort($sourceStrings, SORT_STRING | SORT_FLAG_CASE);

        // 3. Gabungkan string dengan pipe
        $combinedString = implode('|', $sourceStrings);

        // 4. Generate signature menggunakan HMAC-SHA256
        $generatedSignature = hash_hmac('sha256',
            $combinedString,
            env('BILLPLZ_SIGNATURE')
        );

        // 5. Bandingkan dengan x_signature dari request
        return hash_equals($xSignature, $generatedSignature);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillStoreRequest $request)
    {
        $validated = $request->validated();

        $billData = [
            'collection_id' => env('BILLPLZ_COLLECTION'),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'amount' => $validated['amount'] * 100,
            'description' => $validated['description'],
            'due_at' => $validated['due_date'],
            'deliver' => true,
            'callback_url' => env('BILLPLZ_CALLBACK'),
            'reference_1_label' => $validated['reference_1_label'],
            'reference_1' => $validated['reference_1'],
        ];

        $response = Http::withBasicAuth(env('BILLPLZ_KEY'), '')
            ->post('https://www.billplz.com/api/v3/bills', $billData);

        if ($response->successful()) {
            $responseData = $response->json();
            $billing = Billing::find($validated['bill_id']);
            $billing->update([
                'billz_id' => $responseData['id'],
                'total_cost' => $validated['amount'],
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Bill created successfully.',
                'data' => $responseData,
                'bill_url' => $responseData['url'] . '?auto_submit=true',
            ], 201);
        } else {
            return response()->json(['error' => $response->json()], $response->status());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Billing $billing)
    {
        $billing->load(['appointment.doctor', 'appointment.clinic', 'injections', 'service', 'medications', 'procedures', 'investigations', 'appointment.patient']);
        return response()->json([
            'status' => 'success',
            'message' => 'Succesfully fetch bill!',
            'data' => $billing,
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Billing $billing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billing $billing)
    {
        //
    }
}
