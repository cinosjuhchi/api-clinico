<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MessageClinico;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreMessageClinicoRequest;
use App\Http\Requests\UpdateMessageClinicoRequest;

class MessageClinicoController extends Controller
{
    public function sendMessage(StoreMessageClinicoRequest $request)
    {
        $validated = $request->validated();

        $message = MessageClinico::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        return response()->json($message, 201);
    }

    public function getMessages(User $user)
    {        
        $messages = MessageClinico::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages, 201);
    }
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
    public function store(StoreMessageClinicoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MessageClinico $messageClinico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MessageClinico $messageClinico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessageClinicoRequest $request, MessageClinico $messageClinico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MessageClinico $messageClinico)
    {
        //
    }
}
