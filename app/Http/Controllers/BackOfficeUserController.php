<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BackOfficeUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('patients')->paginate();
        return response()->json($users);
    }

    public function patients()
    {
        $patient = Patient::with(['demographics', 'user'])->paginate();

        return response()->json($patient);
    }

    public function getTotal()
    {
        $users = User::all();
        $totalUsers = $users->count();

        return response()->json($totalUsers);
    }

    public function getTotalUsers()
    {
        // Get the current year
        $currentYear = now()->year;

        // Query to count the total users registered each month for the current year
        $totalUsersByMonth = User::selectRaw('COUNT(*) as total_users, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Generate all months of the year with user counts, including months with zero users
        $months = collect(range(1, 12))->map(function ($month) use ($totalUsersByMonth) {
            $date = now()->setMonth($month)->startOfMonth()->format('Y-m');
            $totalUsers = $totalUsersByMonth->firstWhere('month', $date)['total_users'] ?? 0;
            return ['month' => $date, 'total_users' => $totalUsers];
        });

        // Return the result as a JSON response
        return response()->json($months);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
