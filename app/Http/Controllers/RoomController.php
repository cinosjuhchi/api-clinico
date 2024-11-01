<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;

        $rooms = $clinic->rooms()->with(['occupant'])->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved',
            'data' => $rooms,
        ]);
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
    public function store(StoreRoomRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        $collision = Room::where('name', $validated['name'])->where('room_number', $validated['room_number'])->first();
        if ($collision) {
            return response()->json(['status' => 'error', 'message' => 'Room with this name and number already exists.'], 422);
        }
        try {
            Room::create($validated);
            DB::commit();
            return response()->json([
                'status' => 'success',
                "message" => "Success store data.",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $validated = $request->validated();
        $collision = Room::where('name', $validated['name'])->where('room_number', $validated['room_number'])->first();
        if ($collision) {
            return response()->json(['status' => 'error', 'message' => 'Room with this name and number already exists.'], 422);
        }

        $room->fill($validated);
        if ($room->isDirty()) {
            DB::beginTransaction();
            try {
                $room->save();
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully update data.',
                ], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fail update the data.',
                ], 500);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Nothing to update',
        ], 200);

    }

    public function roomResource(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;

        $rooms = $clinic->rooms()->with(['occupant'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved',
            'data' => $rooms,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        DB::beginTransaction();
        try {
            $room->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success delete data.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
