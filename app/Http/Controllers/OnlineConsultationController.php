<?php

namespace App\Http\Controllers;

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
        $onlineConsultation = $user->patientOnlineConsultation()->whereHas('bill', function($query) {
            $query->where('is_paid', true);
        })
        ->with(['doctorRelation.doctor'])
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully get online consultation',
            'data' => $onlineConsultation
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
        //
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
