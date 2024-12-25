<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClaimItem;
use Illuminate\Http\Request;

class ClaimItemController extends Controller
{
    public function index()
    {
        $claimItems = ClaimItem::all();

        return response()->json([
            'message' => 'get Claim items.',
            'data' => $claimItems
        ]);
    }

    public function show(int $id)
    {
        $claimItem = ClaimItem::find($id);
        if (!$claimItem) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim item not found.',
                'id' => $id
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Get Claim item.',
            'data' => $claimItem,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $claimItem = ClaimItem::create(['name' => $request->name]);

        return response()->json([
            'status' => 'success',
            'message' => 'Claim item created.',
            'data' => $claimItem,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['name' => 'required']);
        $claimItem = ClaimItem::find($id);
        if (!$claimItem) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim item not found.',
                'id' => $id
            ], 404);
        }

        $claimItem->update(['name' => $request->name]);

        return response()->json([
            'status' => 'success',
            'message' => 'Claim item updated.',
            'data' => $claimItem,
        ]);
    }

    public function destroy(int $id)
    {
        $claimItem = ClaimItem::find($id);
        if (!$claimItem) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Claim item not found.',
                'id' => $id
            ], 404);
        }
        $claimItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Claim item deleted.',
        ]);
    }
}
