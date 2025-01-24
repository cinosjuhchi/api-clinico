<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorClinicRequest;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\ClinicResource;
use App\Models\Billing;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicDataController extends Controller
{

    public function me(Request $request)
    {
        $user   = Auth::user();
        $clinic = Clinic::with([
            'rooms',
            'location',
            'schedule',
            'user',
            'services',
            'financial',
            'images',
            'doctors.category',
        ])
            ->where('user_id', $user->id)
            ->firstOrFail();
        return response()->json([
            'status'  => 'success',
            'message' => 'Fetch profile success',
            'data'    => new ClinicResource($clinic),
        ]);
    }

    public function medicines(Request $request)
    {
        $user      = Auth::user();
        $clinic    = Clinic::where('user_id', $user->id)->firstOrFail();
        $medicines = $clinic->medications;
        return response()->json($medicines);
    }

    public function pendingAppointmentsDoctor(Request $request)
    {
        $user   = Auth::user();
        $doctor = $user->doctor;
        if (! $doctor) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'user not found',
            ], 404);
        }
        $appointments = $doctor->pendingAppointments()->paginate(5);

        return response()->json($appointments);
    }

    public function consultationAppointments(Request $request)
    {
        $user   = Auth::user();
        $clinic = $user->clinic;

        if (! $clinic) {
            $doctor = $user->doctor;
            if ($doctor) {
                $clinic = $doctor->clinic;
            }
        }

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'user not found',
            ], 404);
        }

        $appointments = $clinic->consultationAppointments()->paginate(5);

        return response()->json($appointments);
    }

    public function storeDoctor(StoreDoctorClinicRequest $request)
    {
        $validated = $request->validated();
        $user      = Auth::user();
        $clinic    = $user->clinic;

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Clinic not found for the user',
            ]);
        }

        try {
            DB::beginTransaction();
            // Create user
            $newUser = User::create([
                'email'        => $validated['email'],
                'password'     => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'],
                'role'         => 'doctor',
            ]);

            $newEmployee = Employee::create([
                'image_profile'   => $validated['image_profile']
                ? $validated['image_profile']->store('image_profile')
                : 'image_profile/default.png',
                'image_signature' => $validated['image_signature']
                ? $validated['image_signature']->store('image_signature')
                : 'path/to/default_signature_image.jpg',
                'branch'          => $validated['branch'],
                'apc'             => $validated['apc'],
                'mmc'             => $validated['mmc'],
                'position'        => $validated['position'],
                'staff_id'        => $validated['staff_id'],
                'tenure'          => $validated['tenure'],
                'basic_salary'    => $validated['basic_salary'],
                'elaun'           => $validated['elaun'],
            ]);

            // Create doctor profile
            $newDoctor = $clinic->doctors()->create([
                'user_id'     => $newUser->id,
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'],
                'employee_id' => $newEmployee->id,
            ]);

            // Create related information
            $newDoctor->demographic()->create([
                'name'           => $validated['name'],
                'nric'           => $validated['nric'],
                'birth_date'     => $validated['birth_date'],
                'place_of_birth' => $validated['place_of_birth'],
                'marital_status' => $validated['marital_status'],
                'email'          => $validated['email'],
                'phone_number'   => $validated['phone_number'],
                'address'        => $validated['address'],
                'country'        => $validated['country'],
                'postal_code'    => $validated['postal_code'],
                'gender'         => $validated['gender'],
            ]);

            $newDoctor->educational()->create([
                'graduated_from'  => $validated['graduated_from'],
                'bachelor'        => $validated['bachelor'],
                'graduation_year' => $validated['graduation_year'],
            ]);

            $newDoctor->contributionInfo()->create([
                'kwsp_number'    => $validated['kwsp_number'],
                'kwsp_amount'    => $validated['kwsp_amount'],
                'perkeso_number' => $validated['perkeso_number'],
                'perkeso_amount' => $validated['perkeso_amount'],
                'tax_number'     => $validated['tax_number'],
                'tax_amount'     => $validated['tax_amount'],
            ]);

            $newDoctor->emergencyContact()->create([
                'name'         => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
            ]);

            $newDoctor->spouseInformation()->create([
                'name'       => $validated['spouse_name'],
                'contact'    => $validated['spouse_phone'],
                'occupation' => $validated['spouse_occupation'],
            ]);

            if (! empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $newDoctor->childsInformation()->create([
                        'name' => $child['name'],
                        'age'  => $child['age'],
                    ]);
                }
            }

            $newDoctor->parentInformation()->create([
                'father_name'       => $validated['father_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_name'       => $validated['mother_name'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact'    => $validated['father_contact'],
                'mother_contact'    => $validated['mother_contact'],
            ]);

            $newDoctor->reference()->create([
                'name'         => $validated['reference_name'],
                'company'      => $validated['reference_company'],
                'number_phone' => $validated['reference_phone'],
                'position'     => $validated['reference_position'],
                'email'        => $validated['reference_email'],
            ]);

            $newDoctor->basicSkills()->create([
                'languange_spoken'  => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office'  => $validated['microsoft_office_skill'],
                'others'            => $validated['others_skill'],
            ]);

            $newDoctor->financialInformation()->create([
                'bank_name'      => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

            DB::commit(); // Ensure all changes are saved to the database

            return response()->json([
                'status'  => 'success',
                'message' => 'Doctor created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create doctor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getSettlements(Request $request)
    {
        $user   = Auth::user();
        $clinic = $user->clinic;

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Clinic not found for the user',
            ], 404);
        }

        // Query settlements untuk clinic yang terkait
        $query = $clinic->settlements()->with(['clinic.financial']);

        // Tambahkan fitur pencarian berdasarkan clinico_id
        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('clinico_id', 'like', '%' . $searchTerm . '%');
        }

        // Paginasi hasil
        $data = $query->paginate();

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function storeStaff(StoreStaffRequest $request)
    {
        $validated = $request->validated();
        $user      = Auth::user();
        $clinic    = $user->clinic;

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Clinic not found for the user',
            ]);
        }

        try {
            DB::beginTransaction();
            // Create user
            $newUser = User::create([
                'email'        => $validated['email'],
                'password'     => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'],
                'role'         => 'staff',
            ]);

            $newEmployee = Employee::create([
                'image_profile'   => $validated['image_profile']
                ? $validated['image_profile']->store('image_profile')
                : 'image_profile/default.png',
                'image_signature' => $validated['image_signature']
                ? $validated['image_signature']->store('image_signature')
                : 'path/to/default_signature_image.jpg',
                'branch'          => $validated['branch'],
                'apc'             => $validated['apc'],
                'mmc'             => $validated['mmc'],
                'position'        => $validated['position'],
                'staff_id'        => $validated['staff_id'],
                'tenure'          => $validated['tenure'],
                'basic_salary'    => $validated['basic_salary'],
                'elaun'           => $validated['elaun'],
            ]);

            // Create doctor profile
            $newStaff = $clinic->staffs()->create([
                'user_id'     => $newUser->id,
                'name'        => $validated['name'],
                'employee_id' => $newEmployee->id,
            ]);

            // Create related information
            $newStaff->demographic()->create([
                'name'           => $validated['name'],
                'nric'           => $validated['nric'],
                'birth_date'     => $validated['birth_date'],
                'place_of_birth' => $validated['place_of_birth'],
                'marital_status' => $validated['marital_status'],
                'email'          => $validated['email'],
                'phone_number'   => $validated['phone_number'],
                'address'        => $validated['address'],
                'country'        => $validated['country'],
                'postal_code'    => $validated['postal_code'],
                'gender'         => $validated['gender'],
            ]);

            $newStaff->educational()->create([
                'graduated_from'  => $validated['graduated_from'],
                'bachelor'        => $validated['bachelor'],
                'graduation_year' => $validated['graduation_year'],
            ]);

            $newStaff->contributionInfo()->create([
                'kwsp_number'    => $validated['kwsp_number'],
                'kwsp_amount'    => $validated['kwsp_amount'],
                'perkeso_number' => $validated['perkeso_number'],
                'perkeso_amount' => $validated['perkeso_amount'],
                'tax_number'     => $validated['tax_number'],
                'tax_amount'     => $validated['tax_amount'],
            ]);

            $newStaff->emergencyContact()->create([
                'name'         => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
            ]);

            $newStaff->spouseInformation()->create([
                'name'       => $validated['spouse_name'],
                'contact'    => $validated['spouse_phone'],
                'occupation' => $validated['spouse_occupation'],
            ]);

            if (! empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $newStaff->childsInformation()->create([
                        'name' => $child['name'],
                        'age'  => $child['age'],
                    ]);
                }
            }

            $newStaff->parentInformation()->create([
                'father_name'       => $validated['father_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_name'       => $validated['mother_name'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact'    => $validated['father_contact'],
                'mother_contact'    => $validated['mother_contact'],
            ]);

            $newStaff->reference()->create([
                'name'         => $validated['reference_name'],
                'company'      => $validated['reference_company'],
                'number_phone' => $validated['reference_phone'],
                'position'     => $validated['reference_position'],
                'email'        => $validated['reference_email'],
            ]);

            $newStaff->basicSkills()->create([
                'languange_spoken'  => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office'  => $validated['microsoft_office_skill'],
                'others'            => $validated['others_skill'],
            ]);

            $newStaff->financialInformation()->create([
                'bank_name'      => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

            DB::commit(); // Ensure all changes are saved to the database

            return response()->json([
                'status'  => 'success',
                'message' => 'Doctor created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create doctor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function consultations(Request $request)
    {
        $user   = Auth::user();
        $clinic = $user->clinic;

        $appointments = $clinic->consultationAppointments()->with(['service', 'bill', 'room'])->paginate(10);
        return response()->json([
            'status'  => 'success',
            'message' => 'Success fetch data',
            'data'    => $appointments,
        ]);
    }

    public function updateDoctor(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name'                       => 'required|string',
            'phone_number'               => 'required|string|min:10',
            'category_id'                => 'required|exists:categories,id',
            // Demographic Information
            'nric'                       => 'required|string|min:5',
            'birth_date'                 => 'required|date|before:today',
            'place_of_birth'             => 'required|string',
            'marital_status'             => 'required|string',
            'address'                    => 'required|string',
            'country'                    => 'required|string',
            'postal_code'                => 'required|numeric|digits_between:4,10',
            'gender'                     => 'required|string',
            // Educational Information
            'graduated_from'             => 'required|string',
            'bachelor'                   => 'required|string',
            'graduation_year'            => 'required|integer',
            // Reference Information
            'reference_name'             => 'required|string',
            'reference_company'          => 'required|string',
            'reference_position'         => 'required|string',
            'reference_phone'            => 'required|string',
            'reference_email'            => 'required|email',
            // Basic Skill Information
            'languange_spoken_skill'     => 'required|string',
            'languange_written_skill'    => 'required|string',
            'microsoft_office_skill'     => 'required|string',
            'others_skill'               => 'required|string',
            // Employment Information
            'image_profile'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_signature'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'branch'                     => 'required|string',
            'position'                   => 'required|string',
            'mmc'                        => 'required|integer',
            'apc'                        => 'required|string',
            'staff_id'                   => 'required|string',
            'tenure'                     => 'required|string',
            'basic_salary'               => 'required|numeric|max:99999999',
            'elaun'                      => 'required|numeric|max:99999999',
            // Financial Information
            'bank_name'                  => 'required|string',
            'account_number'             => 'required|string|max:20',
            // Contribution Info
            'kwsp_number'                => 'required|integer',
            'kwsp_amount'                => 'required|numeric',
            'perkeso_number'             => 'required|integer',
            'perkeso_amount'             => 'required|numeric',
            'tax_number'                 => 'required|string',
            'tax_amount'                 => 'required|numeric',
            // Emergency Contact
            'emergency_contact'          => 'required|string',
            'emergency_contact_number'   => 'required|string|min:10',
            'emergency_contact_relation' => 'required|string',
            // Spouse Information
            'spouse_name'                => 'nullable|string',
            'spouse_occupation'          => 'nullable|string',
            'spouse_phone'               => 'nullable|string',
            // Child Information
            'childs'                     => 'array|nullable',
            'childs.*.name'              => 'required|string',
            'childs.*.age'               => 'required|integer',
            // Parent Information
            'father_name'                => 'required|string',
            'mother_name'                => 'required|string',
            'father_occupation'          => 'required|string',
            'mother_occupation'          => 'required|string',
            'father_contact'             => 'required|string|min:10',
            'mother_contact'             => 'required|string|min:10',
        ]);

        $user   = Auth::user();
        $clinic = $user->clinic;

        if (! $clinic) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Clinic not found for the user',
            ]);
        }

        try {
            DB::beginTransaction();

            // Update main doctor information
            $fieldsToUpdate = [
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'],
            ];
            $doctor->update($fieldsToUpdate);

            // Employee information updates with conditional checks
            $employeeFieldsToUpdate = [
                'branch'       => $validated['branch'],
                'position'     => $validated['position'],
                'apc'          => $validated['apc'],
                'mmc'          => $validated['mmc'],
                'staff_id'     => $validated['staff_id'],
                'tenure'       => $validated['tenure'],
                'basic_salary' => $validated['basic_salary'],
                'elaun'        => $validated['elaun'],
            ];

            if ($request->hasFile('image_profile')) {
                $employeeFieldsToUpdate['image_profile'] = $request->file('image_profile')->store('image_profile');
            }

            if ($request->hasFile('image_signature')) {
                $employeeFieldsToUpdate['image_signature'] = $request->file('image_signature')->store('image_signature');
            }

            $doctor->employmentInformation()->update($employeeFieldsToUpdate);
            $user = $doctor->user;

            // Update related information (demographic, educational, etc.)
            $doctor->demographic()->updateOrCreate([], [
                'nric'           => $validated['nric'],
                'name'           => $validated['name'],
                'email'          => $user->email,
                'birth_date'     => $validated['birth_date'],
                'place_of_birth' => $validated['place_of_birth'],
                'marital_status' => $validated['marital_status'],
                'phone_number'   => $validated['phone_number'],
                'address'        => $validated['address'],
                'country'        => $validated['country'],
                'postal_code'    => $validated['postal_code'],
                'gender'         => $validated['gender'],
            ]);

            $doctor->educational()->updateOrCreate([], [
                'graduated_from'  => $validated['graduated_from'],
                'bachelor'        => $validated['bachelor'],
                'graduation_year' => $validated['graduation_year'],
            ]);

            $doctor->contributionInfo()->updateOrCreate([], [
                'kwsp_number'    => $validated['kwsp_number'],
                'kwsp_amount'    => $validated['kwsp_amount'],
                'perkeso_number' => $validated['perkeso_number'],
                'perkeso_amount' => $validated['perkeso_amount'],
                'tax_number'     => $validated['tax_number'],
                'tax_amount'     => $validated['tax_amount'],
            ]);

            $doctor->emergencyContact()->updateOrCreate([], [
                'name'         => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
            ]);

            // Optional spouse information
            if (! empty($validated['spouse_name'])) {
                $doctor->spouseInformation()->updateOrCreate([], [
                    'name'       => $validated['spouse_name'],
                    'occupation' => $validated['spouse_occupation'],
                    'contact'    => $validated['spouse_phone'],
                ]);
            }

            // Child information
            $doctor->childsInformation()->delete();
            if (! empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $doctor->childsInformation()->create([
                        'name' => $child['name'],
                        'age'  => $child['age'],
                    ]);
                }
            }

            // Parent information
            $doctor->parentInformation()->updateOrCreate([], [
                'father_name'       => $validated['father_name'],
                'mother_name'       => $validated['mother_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact'    => $validated['father_contact'],
                'mother_contact'    => $validated['mother_contact'],
            ]);

            // Reference information
            $doctor->reference()->updateOrCreate([], [
                'name'         => $validated['reference_name'],
                'company'      => $validated['reference_company'],
                'number_phone' => $validated['reference_phone'],
                'position'     => $validated['reference_position'],
                'email'        => $validated['reference_email'],
            ]);

            // Basic skills information
            $doctor->basicSkills()->updateOrCreate([], [
                'languange_spoken'  => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office'  => $validated['microsoft_office_skill'],
                'others'            => $validated['others_skill'],
            ]);

            // Financial information
            $doctor->financialInformation()->updateOrCreate([], [
                'bank_name'      => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Doctor updated successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update doctor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function doctors(Request $request)
    {
        $user   = Auth::user();
        $clinic = $user->clinic;
        if (! $clinic) {
            $clinic = $user->doctor->clinic;
            if (! $clinic) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'user not found',
                ]);
            }

        }
        $doctors = $clinic->doctors()->with(['employmentInformation',
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
            'category'])->paginate(10);
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetch data',
            'data'    => $doctors,
        ], 200);
    }

    public function showDoctor(Doctor $doctor)
    {
        $doctor->load([
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
        ]);
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetch data',
            'data'    => $doctor,
        ], 200);
    }

    public function destroyDoctor(Doctor $doctor)
    {
        try {
            DB::beginTransaction();
            $doctor->user()->delete();
            return response()->json([
                'status'  => 'success',
                'message' => 'Doctor deleted',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'failed',
                'message' => 'something wrong happen',
            ]);
        }
    }
    public function destroyStaff(Staff $staff)
    {
        try {
            DB::beginTransaction();
            $staff->user()->delete();
            return response()->json([
                'status'  => 'success',
                'message' => 'Staff deleted',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'failed',
                'message' => 'something wrong happen',
            ]);
        }
    }

    public function staffs(Request $request)
    {
        $user   = Auth::user();
        $clinic = $user->clinic;
        if (! $clinic) {
            $clinic = $user->staff->clinic;
            if (! $clinic) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'user not found',
                ]);
            }

        }
        $staffsQuery = $clinic->staffs()->with(['employmentInformation',
            'educational',
            'clinic.schedule',
            'demographic',
            'contributionInfo',
            'emergencyContact',
            'spouseInformation',
            'childsInformation',
            'parentInformation',
            'reference',
            'basicSkills',
            'financialInformation']);

        $paginate = filter_var($request->input('paginate', 'true'), FILTER_VALIDATE_BOOLEAN);

        $staffs = $paginate
        ? $staffsQuery->paginate($request->input('per_page', 10))
        : $staffsQuery->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetch data',
            'data'    => $staffs,
        ], 200);
    }

    public function showStaff(Staff $staff)
    {
        $staff->load([
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
        ]);
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully fetch data',
            'data'    => $staff,
        ], 200);
    }

    public function bills(Request $request)
    {
        $data = Billing::all()->paginate(10);
        return response()->json($data);

    }

}
