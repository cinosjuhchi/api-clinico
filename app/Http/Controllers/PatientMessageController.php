<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientMessage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePatientMessageRequest;
use App\Http\Requests\UpdatePatientMessageRequest;

class PatientMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = PatientMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json($message, 201);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $messages = PatientMessage::where(function ($query) use ($request) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->user_id)
                ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
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
    public function store(StorePatientMessageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientMessage $patientMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientMessage $patientMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientMessageRequest $request, PatientMessage $patientMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientMessage $patientMessage)
    {
        //
    }

}
