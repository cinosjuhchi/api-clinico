<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        if (!$clinic) {
            $clinic = $user->doctor->clinic;
            if (!$clinic) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user not found',
                ]);
            }
        }

        $page = $request->input('page', 1);
        $perPage = 10;

        $doctorsQuery = $clinic->doctors()
            ->with([
                'employmentInformation',
                'educational',
                'demographic',
                'contributionInfo',
                'emergencyContact',
                'spouseInformation',
                'childsInformation',
                'parentInformation',
                'reference',
                'basicSkills',
                'financialInformation',
                'category',
            ])
            ->select('doctors.*', DB::raw("'doctor' as type"));

        $staffQuery = $clinic->staffs()
            ->with([
                'employmentInformation',
                'educational',
                'demographic',
                'contributionInfo',
                'emergencyContact',
                'spouseInformation',
                'childsInformation',
                'parentInformation',
                'reference',
                'basicSkills',
                'financialInformation',
                'category',
            ])
            ->select('staff.*', DB::raw("'staff' as type"));

        $employees = $doctorsQuery
            ->union($staffQuery)
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $employees,
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
    public function store(StoreEmployeeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            // Hapus employee dari database
            $employee->delete();

            // Mengembalikan respons sukses
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Menangani kesalahan yang mungkin terjadi saat penghapusan
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the employee: ' . $e->getMessage(),
            ], 500);
        }
    }
}
