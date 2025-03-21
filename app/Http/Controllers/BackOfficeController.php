<?php
namespace App\Http\Controllers;

use App\Helpers\GenerateStaffIdHelper;
use App\Http\Requests\BackOfficeRequest;
use App\Http\Requests\StoreAdminRequest;
use App\Models\AdminClinico;
use App\Models\BoChildren;
use App\Models\BoContributionInfo;
use App\Models\BoDemographic;
use App\Models\BoEmergencyContact;
use App\Models\BoFinancial;
use App\Models\BoParent;
use App\Models\BoSpouseInformation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackOfficeController extends Controller
{

    public function index(Request $request)
    {
        $query = AdminClinico::with([
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
            'schedules',
            'user.referralCode',
            'user.referredBy.user.clinic'
        ])
        ->with(['user' => function($query) {
            $query->withCount('referredBy');
        }]);

        // Tambahkan filter untuk pencarian berdasarkan name
        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        // Ambil data dengan paginasi
        $data = $query->paginate();

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function show($id)
    {
        $adminClinico = AdminClinico::with([
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
            'schedules',
            'user.referralCode',
            'user.referredBy.user.clinic',
            'user.referredBy.affiliated',
        ])
        ->with(['user' => function($query) {
            $query->withCount('referredBy');
        }])
        ->find($id);

        if (!$adminClinico) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data not found',
            ], 404);
        }

        // Tambahkan data bulan ke setiap referred_by
        $referredByData = $adminClinico->user->referredBy->map(function ($referred) {
            $affiliated = optional($referred)->affiliated;

            // Loop untuk bulan 1 sampai 18
            $months = collect(range(1, 18))->mapWithKeys(function ($month) use ($affiliated) {
                $monthData = optional($affiliated)->firstWhere('month', $month);
                return ["month_$month" => $monthData ? $monthData->status : "pending"];
            });

            // Gabungkan months ke dalam referred_by
            return array_merge($referred->toArray(), ['months' => $months]);
        });

        return response()->json([
            'status' => 'success',
            'data'   => array_merge($adminClinico->toArray(), ['user' => [
                'referral_code' => $adminClinico->user->referralCode,
                'referred_by'   => $referredByData
            ]])
        ]);
    }





    public function login(BackOfficeRequest $request)
    {
        $request->validated();
        if (Auth::attempt(['email' => $request->user, 'password' => $request->password]) || Auth::attempt(['phone_number' => $request->user, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->role == 'superadmin') {
                $token = $user->createToken('Clinico', ['superadmin', 'hasAccessResource', 'backOffice'])->plainTextToken;
                $role  = $user->role;
                return response()->json([$user, 'role' => $role, 'token' => $token], 200);

            }
            if ($user->role == 'admin') {
                $token = $user->createToken('Clinico', ['admin', 'hasAccessResource', 'backOffice'])->plainTextToken;
                $role  = $user->role;
                return response()->json([$user, 'role' => $role, 'token' => $token], 200);
            }
        }

        return response()->json(["message" => "User didn't exist!"], 404);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => "Logout"], 200);
    }

    public function me()
    {
        $user = Auth::user()->load(
            'referredBy',
            'referralCode',
            'adminClinico.demographic',
            'adminClinico.contributionInfo',
            'adminClinico.employmentInformation',
            'adminClinico.financialInformation',
            'adminClinico.emergencyContact',
            'adminClinico.spouseInformation',
            'adminClinico.childsInformation',
            'adminClinico.parentInformation',
        );
        return response()->json([[
            'status'  => 'success',
            'message' => 'get current user',
            'data'    => $user,
        ]]);
    }

    public function storeStaff(StoreAdminRequest $request)
    {
        try {
            DB::beginTransaction();

            // Get validated data
            $validated = $request->validated();

            // Create user
            $user = User::create([
                'email'        => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password'     => bcrypt($validated['password']),
                'role'         => $validated['role'],
            ]);

            // Generate staff_id using helper
            $staffId = GenerateStaffIdHelper::generate();

            // Create employee
            $employee = Employee::create([
                'image_profile'   => $validated['image_profile']
                ? $validated['image_profile']->store('image_profile')
                : 'image_profile/default.png',
                'image_signature' => $validated['image_signature']
                ? $validated['image_signature']->store('image_signature')
                : 'path/to/default_signature_image.jpg',
                'branch'          => $validated['branch'],
                'position'        => $validated['position'],
                'mmc'             => $validated['mmc'],
                'apc'             => $validated['apc'],
                'staff_id'        => $staffId,
                'tenure'          => $validated['tenure'],
                'basic_salary'    => $validated['basic_salary'],
                'elaun'           => $validated['elaun'],
            ]);

            // Get clinic
            if ($validated['is_doctor'] == 'true') {
                $staff = AdminClinico::create([
                    'name'        => $validated['name'],
                    'user_id'     => $user->id,
                    'employee_id' => $employee->id,
                    'is_doctor'   => true,
                    'department'  => $validated['department'],
                ]);
            } else {
                $staff = AdminClinico::create([
                    'name'        => $validated['name'],
                    'user_id'     => $user->id,
                    'employee_id' => $employee->id,
                    'is_doctor'   => false,
                    'department'  => $validated['department'],
                ]);

            }
            // Create staff

            // Create staff demographics
            $demographic = BoDemographic::create([
                'name'             => $staff->name,
                'birth_date'       => $validated['birth_date'],
                'place_of_birth'   => $validated['place_of_birth'],
                'gender'           => $validated['gender'],
                'marital_status'   => $validated['marital_status'],
                'nric'             => $validated['nric'],
                'address'          => $validated['address'],
                'country'          => $validated['country'],
                'postal_code'      => $validated['postal_code'],
                'email'            => $user->email,
                'phone_number'     => $user->phone_number,
                'admin_clinico_id' => $staff->id,
            ]);

            // Create staff contributions
            $contribution = BoContributionInfo::create([
                'kwsp_number'      => $validated['kwsp_number'],
                'kwsp_amount'      => $validated['kwsp_amount'],
                'perkeso_number'   => $validated['perkeso_number'],
                'perkeso_amount'   => $validated['perkeso_amount'],
                'tax_number'       => $validated['tax_number'],
                'tax_amount'       => $validated['tax_amount'],
                'eis'              => $validated['eis'],
                'admin_clinico_id' => $staff->id,
            ]);

            // Create staff financial information
            $financial = BoFinancial::create([
                'bank_name'        => $validated['bank_name'],
                'account_number'   => $validated['account_number'],
                'admin_clinico_id' => $staff->id,
            ]);

            // Create staff Emergency
            $emergency = BoEmergencyContact::create([
                'name'         => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
                'admin_clinico_id' => $staff->id,
            ]);

            // Create staff Spouse
            if (!empty($validated['spouse_name'])) {
                $spouseInformation = BoSpouseInformation::create([
                    'name'       => $validated['spouse_name'],
                    'occupation' => $validated['spouse_occupation'],
                    'contact'    => $validated['spouse_phone'],
                    'admin_clinico_id' => $staff->id,
                ]);
            }

            // Create staff child
            if (!empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $childsInformation = BoChildren::create([
                        'name' => $child['name'],
                        'occupation'  => $child['occupation'],
                        'contact'  => $child['contact'],
                        'admin_clinico_id' => $staff->id,
                    ]);
                }
            }

            // Create staff parent
            $parentInformation = BoParent::create([
                'father_name'       => $validated['father_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_name'       => $validated['mother_name'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact'    => $validated['father_contact'],
                'mother_contact'    => $validated['mother_contact'],
                'admin_clinico_id'  => $staff->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Staff created successfully',
                'data'    => [
                    'user'  => $user,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create staff',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function growthOfRegistration(Request $request)
    {
        $type = $request->query('type');
        $year = $request->query('year', now()->year);

        $patientData = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => 0];
        });
        $clinicData = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => 0];
        });

        if (!$type || $type === 'patient') {
            $patients = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->where('role', 'user')
                ->whereYear('created_at', $year)
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->get();

            $patients->each(function ($item) use (&$patientData) {
                $patientData[$item->month] = $item->total;
            });
        }

        if (!$type || $type === 'clinic') {
            $clinics = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->where('role', 'clinic')
                ->whereYear('created_at', $year)
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->get();

            $clinics->each(function ($item) use (&$clinicData) {
                $clinicData[$item->month] = $item->total;
            });
        }

        $result = [];
        if (!$type) {
            $result = [
                'patient' => $patientData,
                'clinic' => $clinicData,
            ];
        } elseif ($type === 'patient') {
            $result = $patientData;
        } elseif ($type === 'clinic') {
            $result = $clinicData;
        }

        return response()->json([
            "status" => "success",
            "message" => "Get growth of registration (" . $year . ")" . ($type ? " for $type" : ""),
            "data" => $result
        ]);
    }
}
