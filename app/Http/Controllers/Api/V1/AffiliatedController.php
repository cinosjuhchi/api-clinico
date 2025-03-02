<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Affiliated;
use App\Models\Referral;
use Illuminate\Http\Request;

class AffiliatedController extends Controller
{
    public function index()
    {
        $referrals = Referral::with('affiliated')->get();

        // Format data agar frontend mudah menampilkan bulan 1-18
        $referrals = $referrals->map(function ($referral) {
            $months = collect(range(1, 18))->mapWithKeys(function ($month) use ($referral) {
                $affiliated = $referral->affiliated->firstWhere('month', $month);
                return ["month_$month" => $affiliated ? $affiliated->status : "pending"];
            });

            return [
                'id' => $referral->id,
                // 'name' => $referral->name,
            ] + $months->toArray();
        });

        return response()->json($referrals);
    }

    // api/v1/affiliated/{referralId}/month/{month}
    public function update(Request $request, $referralId, $month)
    {
        $request->validate([
            'status' => 'required|in:pending,paid',
        ]);

        $referral = Referral::find($referralId);

        $affiliated = Affiliated::updateOrCreate(
            ['referral_id' => $referral->id, 'month' => $month],
            ['status' => $request->status]
        );

        return response()->json(['message' => 'Status updated successfully', 'data' => $affiliated]);
    }
}
