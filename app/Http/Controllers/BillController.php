<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


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
            'data' => $bills
        ]);
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
            'is_paid' => true
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
    public function store(Request $request)
    {
        $validated = $request->validate([            
            'name' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'email' => 'required|email',
            'amount' => 'required|numeric',
            'bill_id' => 'required|exists:billings,id',
            'reference_1_label' => 'nullable|string',
            'reference_1' => 'nullable|string'
        ]);        

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
            ->post('https://www.billplz-sandbox.com/api/v3/bills', $billData);

        if ($response->successful()) {
            $responseData = $response->json();
            $bill = Billing::find($validated['bill_id']);
            $bill->update([
                'billz_id' => $responseData['id'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bill created successfully.',
                'data' => $responseData,
                'bill_url' => $responseData['url']
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
        //
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
