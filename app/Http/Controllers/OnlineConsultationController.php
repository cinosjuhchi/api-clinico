<?php

namespace App\Http\Controllers;

use App\Models\AdminClinico;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OnlineConsultation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOnlineConsultationRequest;
use App\Http\Requests\UpdateOnlineConsultationRequest;

class OnlineConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $doctorClinico = AdminClinico::where('is_doctor', true)->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
            ->orWhere('department', 'like', "%$search%"); // Sesuaikan field dengan database
        })->with(['user'])->paginate(10);
        $onlineConsultation = $user->patientOnlineConsultation()
        ->with(['doctorRelation.doctor'])
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully get online consultation',
            'history_chat' => $onlineConsultation,
            'doctor_clinico' => $doctorClinico
        ], 200);
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
    public function store(StoreOnlineConsultationRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $user->patientOnlineConsultation()->create([
            'doctor' => $validated['doctor_id'],
            'patient_id' => $validated['patient_id'],
        ]);
        return response()->json([
            'status' => 'success',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OnlineConsultation $onlineConsultation)
    {
        $user = Auth::user();
        if($onlineConsultation->patientRelation->id !== $user->id)
        {
            return response()->json([
                'status' => 'Unauthorize',
                'message' => 'Forbidden access.'
            ], 403);
        }
        $messages = $onlineConsultation->chats()->orderBy('created_at', 'asc')->get();    
        return response()->json($messages, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OnlineConsultation $onlineConsultation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOnlineConsultationRequest $request, OnlineConsultation $onlineConsultation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OnlineConsultation $onlineConsultation)
    {
        //
    }
}
