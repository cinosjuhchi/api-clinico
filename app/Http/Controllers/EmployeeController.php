<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\AdminClinico;
use App\Models\ClaimPermission;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $auth = auth()->user();
        $role = $auth->role; // ['superadmin', 'clinic']
        $search = request('q');
        $month = request('month'); // 1, 2, 3, ..., 12
        $year = request('year'); // 2024, 2025, 2026

        // Relasi umum untuk employee
        $commonWithRelations = [
            'user',
            'demographic',
            'educational',
            'contributionInfo',
            'emergencyContact',
            'spouseInformation',
            'childsInformation',
            'parentInformation',
            'reference',
            'basicSkills',
            'financialInformation',
            'employmentInformation',
        ];

        if ($role === 'superadmin') {
            $employeesQuery = AdminClinico::with($commonWithRelations);
            $employeesQuery->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('adminClinico.demographic', function ($subQuery) use ($search) {
                            $subQuery->where('nric', 'like', "%{$search}%");
                        });
                });
            });

            $employees = $this->applyFilters($employeesQuery, $search, $month, $year)->get();

            return response()->json([
                "status" => "success",
                "message" => "List of employees",
                "data" => $employees
            ]);
        }

        if ($role === 'clinic') {
            $employees = $this->clinicEmployees($auth, $commonWithRelations, $search, $month, $year);


            return response()->json([
                "status" => "success",
                "message" => "List of clinic employees with working hours and claims",
                "data" => $employees
            ]);
        }

        return response()->json([
            "status" => "error",
            "message" => "Forbidden"
        ], 403);
    }

    /**
     * Fungsi untuk menambahkan filter pencarian & waktu
     */
    private function applyFilters($query, $search, $month, $year)
    {
        $query->with(['user' => function ($subQuery) use ($month, $year) {
            $subQuery->with(['attendances' => function ($attQuery) use ($month, $year) {
                $attQuery->when($month && $year, function ($query) use ($month, $year) {
                    $query->whereMonth('clock_in', $month)
                        ->whereYear('clock_in', $year);
                });
            }]);

            $subQuery->with(['claimPermissions' => function ($attQuery) use ($month, $year) {
                $attQuery->when($month && $year, function ($query) use ($month, $year) {
                    $query->where('month', $month)
                        ->whereYear('created_at', $year);
                });
            }]);

            // $subQuery->when($month && $year, function ($subQuery) use ($month, $year) {
                $subQuery->withCount([
                    'attendances as total_working_hours_in_month' => function ($query) use ($month, $year) {
                        $query->when($month && $year, function ($query) use ($month, $year) {
                            $query->whereMonth('clock_in', $month)
                                ->whereYear('clock_in', $year);
                        })->select(DB::raw("SUM(total_working_hours)"));
                    },
                    'claimPermissions as total_claims' => function ($query) use ($month, $year) {
                        $query->when($month && $year, function ($query) use ($month, $year) {
                            $query->where('month', $month)
                                ->whereYear('created_at', $year);
                        })->select(DB::raw("SUM(amount)"));
                    }
                ]);
            // });
        }]);

        return $query;
    }

    /**
     * Fungsi untuk mengambil data employees di klinik (doctors & staffs)
     */
    private function clinicEmployees($auth, $relations, $search, $month, $year)
    {
        $doctorsQuery = $auth->clinic->doctors()
            ->with($relations)
            ->with(['bills' => function ($query) use ($month, $year) {
                $query->where('is_paid', true)
                    ->when($month &&  $year, function ($query) use ($month, $year) {
                        $query->whereMonth('transaction_date', $month)
                            ->whereYear('transaction_date', $year);
                    });
            }])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('doctor.demographic', function ($subQuery) use ($search) {
                            $subQuery->where('nric', 'like', "%{$search}%");
                        });
                });
            })
            ->withSum('bills', 'total_cost');

        $staffsQuery = $auth->clinic->staffs()
            ->with($relations)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('staff.demographic', function ($subQuery) use ($search) {
                            $subQuery->where('nric', 'like', "%{$search}%");
                        });
                });
            });

        $doctors = collect($this->applyFilters($doctorsQuery, $search, $month, $year)->get());
        $staffs = collect($this->applyFilters($staffsQuery, $search, $month, $year)->get());

        // Menambahkan sale_incentives (5% dari total bills)
        $doctors->transform(function ($doctor) {
            $doctor->sale_incentives = $doctor->bills_sum_total_cost * 0.05;
            return $doctor;
        });

        return $doctors->merge($staffs);
    }



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
