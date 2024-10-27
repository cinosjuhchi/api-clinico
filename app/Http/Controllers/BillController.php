<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function callback(Request $request)
    {
        $billId = $request->input('id');
        $paid = $request->input('paid');
        $signature = $request->header('X-Signature');

        if (!$this->isValidSignature($signature, $request->all())) {
            return response()->json(['error' => 'Invalid signature.'], 403);
        }
        

        return response()->json(['status' => 'success'], 200);
    }

    protected function isValidSignature($signature, $payload)
    {
        $computedSignature = hash_hmac('sha256', http_build_query($payload), env('BILLPLZ_KEY'));

        return hash_equals($computedSignature, $signature);
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
            'bill_id' => 'required|exists:billings,id',
        ]);

        $billData = [
            'collection_id' => env('BILLPLZ_COLLECTION'),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'amount' => 2,
            'description' => $validated['description'],
            'due_at' => $validated['due_date'],         
            'deliver' => true,   
            'callback_url' => env('BILLPLZ_CALLBACK')
        ];

        $response = Http::withBasicAuth(env('BILLPLZ_KEY'), '')
            ->post('https://www.billplz.com/api/v3/bills', $billData);

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
