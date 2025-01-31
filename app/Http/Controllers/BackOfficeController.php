<?php
namespace App\Http\Controllers;

use App\Helpers\GenerateStaffIdHelper;
use App\Http\Requests\BackOfficeRequest;
use App\Http\Requests\StoreAdminRequest;
use App\Models\AdminClinico;
use App\Models\BoContributionInfo;
use App\Models\BoDemographic;
use App\Models\BoFinancial;
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
        ]);

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
        $user = Auth::user()->load('adminClinico.demographic', 'adminClinico.contributionInfo', 'adminClinico.employmentInformation', 'adminClinico.financialInformation');
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
                'admin_clinico_id' => $staff->id,
            ]);

            // Create staff financial information
            $financial = BoFinancial::create([
                'bank_name'        => $validated['bank_name'],
                'account_number'   => $validated['account_number'],
                'admin_clinico_id' => $staff->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Staff created successfully',
                'data'    => [
                    'user'  => $user,
                    'staff' => $staff->load(['employmentInformation', 'demographic', 'contribution', 'financialInformation']),
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
}
