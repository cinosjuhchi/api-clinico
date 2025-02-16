<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatDoctorBill;
use App\Models\OnlineConsultation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreChatDoctorBillRequest;
use App\Http\Requests\UpdateChatDoctorBillRequest;

class ChatDoctorBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatDoctorBillRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $billData  = [
            'collection_id'     => env('BILLPLZ_COLLECTION'),
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'amount'            => $validated['amount'] * 100,
            'description'       => $validated['description'],
            'due_at'            => $validated['due_date'],
            'deliver'           => true,
            'callback_url'      => env('BILLPLZ_CHAT_CALLBACK'),
            'reference_1_label' => $validated['reference_1_label'],
            'reference_1'       => $validated['reference_1'],
        ];
        $response = Http::withBasicAuth(env('BILLPLZ_KEY'), '')
    ->post('https://www.billplz.com/api/v3/bills', $billData);


    if($response->successful())
    {
        $responseData = $response->json();
        $billing = ChatDoctorBill::create([
            'billz_id' => $responseData['id'],
            'total_cost' => $validated['amount'],            
            'transaction_date' => $validated['transaction_date']
        ]);
        $onlineConsultation = $billing->onlineConsultation()->create([
            'is_confirmed' => 'false',
            'patient' => $user->id,
            'doctor' => $validated['doctor']
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Bill created successfully',
            'data' => $responseData,
            'bill_url' => $responseData['url'] . '?auto_submit=true',
        ], 201);
    }


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

        $billing = ChatDoctorBill::where('billz_id', $billId)->first();
        $billing->update([
            'is_paid' => true,
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
     * Display the specified resource.
     */
    public function show(ChatDoctorBill $chatDoctorBill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatDoctorBill $chatDoctorBill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatDoctorBillRequest $request, ChatDoctorBill $chatDoctorBill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatDoctorBill $chatDoctorBill)
    {
        //
    }
}
