<?php
namespace App\Http\Controllers;

use App\Models\ChatDoctor;
use App\Models\OnlineConsultation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreChatDoctorRequest;
use App\Http\Requests\UpdateChatDoctorRequest;

class ChatDoctorController extends Controller
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
    public function store(OnlineConsultation $onlineConsultation, StoreChatDoctorRequest $request)
    {
        $user = Auth::user();
        if ($onlineConsultation->patientRelation->id !== $user->id) {
            return response()->json([
                'status'  => 'Unauthorize',
                'message' => 'Forbidden access.',
            ], 403);
        }
        $validated = $request->validated();
        $onlineConsultation->chats()->create([
            'message' => $validated['message'],
            'sender_id' => $onlineConsultation->patientRelation->id,
            'receiver_id'  => $onlineConsultation->doctorRelation->id,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Success to send message'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatDoctor $chatDoctor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatDoctor $chatDoctor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatDoctorRequest $request, ChatDoctor $chatDoctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatDoctor $chatDoctor)
    {
        //
    }
}
