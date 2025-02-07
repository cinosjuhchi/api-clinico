<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TopEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $usersQuery = User::with(
                'referredBy.user.clinic',
                'adminClinico.demographic',
                'adminClinico.contributionInfo',
                'adminClinico.employmentInformation',
                'adminClinico.financialInformation')
            ->withCount('referredBy as total_referral')
            ->where('role', 'admin');

        $userID = $request->query("user_id");
        if ($userID) {
            $usersQuery->where('id', $userID);
        }

        $users = $usersQuery->orderBy('total_referral', 'desc')->limit(6)->get();

        return response()->json([
            "status" => "success",
            "message" => "Get top employee (top 6 members)",
            "data" => $users
        ]);
    }
}
