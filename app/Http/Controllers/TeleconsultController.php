<?php
namespace App\Http\Controllers;

use App\Models\ChatDoctor;
use App\Models\OnlineConsultation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TeleconsultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user               = Auth::user();
        $onlineConsultation = $user->doctorOnlineConsultation()->whereHas('bill', function ($query) {
            $query->where('is_paid', true);
        })
            ->with(['patientRelation.patient' => function ($query) {
                $query->first();
            }])
            ->paginate(10);

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully get online consultation',
            'data'    => $onlineConsultation,
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OnlineConsultation $onlineConsultation, Request $request)
    {
        $user = Auth::user();
        if ($onlineConsultation->doctorRelation->id != $user->id || $onlineConsultation->is_confirmed == true) {
            return response()->json([
                'status'  => 'unauthorize',
                'message' => 'Forbidden Access.',
            ], 403);
        }
        $validated = $request->validate([
            'message' => 'required|string',
        ]);
        $onlineConsultation->chats()->create([
            'doctor'  => $onlineConsultation->doctorRelation->id,
            'patient' => $onlineConsultation->patientRelation->id,
            'message' => $validated['message'],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OnlineConsultation $onlineConsultation)
    {
        $user = Auth::user();

        if ($onlineConsultation->doctorRelation->id !== $user->id) {
            return response()->json([
                'status'  => 'Unauthorize',
                'message' => 'Forbidden access.',
            ], 403);
        }

        $messages = $onlineConsultation->chats()
            ->orderBy('created_at', 'asc')
            ->with([
                'patientRelation' => function ($query) {
                    $query->with(['patient.demographic'])->first(); // Ambil hanya satu pasien pertama dengan relasi demographic
                },
            ])
            ->get();

        return response()->json($messages, 200);
    }

    public function complete(OnlineConsultation $onlineConsultation)
    {
        $onlineConsultation->update([
            'is_confirmed' => true
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully update status'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatDoctor $chatDoctor)
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
