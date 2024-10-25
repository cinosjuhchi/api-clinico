<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ClinicResource;
use App\Http\Requests\StoreDoctorClinicRequest;
use App\Http\Requests\UpdateDoctorClinicRequest;

class ClinicDataController extends Controller
{

    public function me(Request $request)
    {
        $user = Auth::user();        
        $clinic = Clinic::with([
            'rooms',
            'location',
            'schedule',
            'services',            
            'doctors.category'
        ])
        ->where('user_id', $user->id)
        ->firstOrFail();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetch profile success',
            'data' => new ClinicResource($clinic)
        ]);
    }

    public function medicines(Request $request)
    {
        $user = Auth::user();
        $clinic = Clinic::where('user_id', $user->id)->firstOrFail();                
        $medicines = $clinic->medications;
        return response()->json($medicines);
    }

    public function pendingAppointmentsDoctor(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;        
        if(!$doctor)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
        }
        $appointments = $doctor->pendingAppointments()->paginate(5);

        return response()->json($appointments);
    }

    public function storeDoctor(StoreDoctorClinicRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Clinic not found for the user'
            ]);
        }

        try {
            DB::beginTransaction();
            // Create user
            $newUser = User::create([
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'phone_number' => $validated['phone_number'],
                'role' => 'doctor',
            ]);

            $newEmployee = Employee::create([
                'image_profile' => $validated['image_profile'] 
                    ? $validated['image_profile']->store('image_profile') 
                    : 'image_profile/default.png',
                'image_signature' => $validated['image_signature'] 
                    ? $validated['image_signature']->store('image_signature') 
                    : 'path/to/default_signature_image.jpg',
                'branch' => $validated['branch'],
                'apc' => $validated['apc'],
                'mmc' => $validated['mmc'],
                'position' => $validated['position'],
                'staff_id' => $validated['staff_id'],
                'tenure' => $validated['tenure'],
                'basic_salary' => $validated['basic_salary'],
                'elaun' => $validated['elaun'],
            ]);

            // Create doctor profile
            $newDoctor = $clinic->doctors()->create([
                'user_id' => $newUser->id,
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'employee_id' => $newEmployee->id,
            ]);

            // Create related information
            $newDoctor->demographic()->create([
                'name' => $validated['name'],
                'nric' => $validated['nric'],
                'birth_date' => $validated['birth_date'],
                'place_of_birth' => $validated['place_of_birth'],
                'marital_status' => $validated['marital_status'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'address' => $validated['address'],
                'country' => $validated['country'],
                'postal_code' => $validated['postal_code'],
                'gender' => $validated['gender'],
            ]);

            $newDoctor->educational()->create([
                'graduated_from' => $validated['graduated_from'],
                'bachelor' => $validated['bachelor'],
                'graduation_year' => $validated['graduation_year'],
            ]);

            $newDoctor->contributionInfo()->create([
                'kwsp_number' => $validated['kwsp_number'],
                'kwsp_amount' => $validated['kwsp_amount'],
                'perkeso_number' => $validated['perkeso_number'],
                'perkeso_amount' => $validated['perkeso_amount'],
                'tax_number' => $validated['tax_number'],
                'tax_amount' => $validated['tax_amount'],
            ]);

            $newDoctor->emergencyContact()->create([
                'name' => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
            ]);

            $newDoctor->spouseInformation()->create([
                'name' => $validated['spouse_name'],
                'contact' => $validated['spouse_phone'],
                'occupation' => $validated['spouse_occupation'],
            ]);

            if (!empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $newDoctor->childsInformation()->create([
                        'name' => $child['name'],
                        'age' => $child['age'],
                    ]);
                }
            }

            $newDoctor->parentInformation()->create([
                'father_name' => $validated['father_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_name' => $validated['mother_name'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact' => $validated['father_contact'],
                'mother_contact' => $validated['mother_contact'],
            ]);

            $newDoctor->reference()->create([
                'name' => $validated['reference_name'],
                'company' => $validated['reference_company'],
                'number_phone' => $validated['reference_phone'],
                'position' => $validated['reference_position'],
                'email' => $validated['reference_email'],
            ]);

            $newDoctor->basicSkills()->create([
                'languange_spoken' => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office' => $validated['microsoft_office_skill'],
                'others' => $validated['others_skill'],
            ]);

            

            $newDoctor->financialInformation()->create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

            DB::commit(); // Ensure all changes are saved to the database

            return response()->json([
                'status' => 'success',
                'message' => 'Doctor created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create doctor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDoctor(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string|min:10',            
            'category_id' => 'required|exists:categories,id',      
            // Demographic Information
            'nric' => 'required|string|min:5',
            'birth_date' => 'required|date:before:today',
            'place_of_birth' => 'required|string',
            'marital_status' => 'required|string',            
            'address' => 'required|string',
            'country' => 'required|string',
            'postal_code' => 'required|numeric|digits_between:4,10',
            'gender' => 'required|string',
            // Educational Information
            'graduated_from' => 'required|string',
            'bachelor' => 'required|string',
            'graduation_year' => 'required|integer',
            // Reference Information
            'reference_name' => 'required|string',
            'reference_company' => 'required|string',
            'reference_position' => 'required|string',
            'reference_phone' => 'required|string',
            'reference_email' => 'required|email',
            // Basic Skill Information
            'languange_spoken_skill' => 'required|string',
            'languange_written_skill' => 'required|string',
            'microsoft_office_skill' => 'required|string',
            'others_skill' => 'required|string',
            // Employment Information
            'image_profile' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_signature' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'branch' => 'required|string',
            'position' => 'required|string',                
            'mmc' => 'required|integer',
            'apc' => 'required|string',
            'staff_id' => 'required|string',
            'tenure' => 'required|string',
            'basic_salary' => 'required|numeric',
            'elaun' => 'required|numeric',
            // Financial Information
            'bank_name' => 'required|string',            
            'account_number' => 'required|string|max:20',
            // Contribution Info
            'kwsp_number' => 'required|integer',
            'kwsp_amount' => 'required|numeric',
            'perkeso_number' => 'required|integer',
            'perkeso_amount' => 'required|numeric',
            'tax_number' => 'required|string',
            'tax_amount' => 'required|numeric',
            // Emergency Contact
            'emergency_contact' => 'required|string',
            'emergency_contact_number' => 'required|string|min:10',
            'emergency_contact_relation' => 'required|string',
            // Spouse Information
            'spouse_name' => 'nullable|string',
            'spouse_occupation' => 'nullable|string',
            'spouse_phone' => 'nullable|string',
            // Child Information
            'childs' => 'array|nullable',
            'childs.*.name' => 'required|string',
            'childs.*.age' => 'required|integer',
            // Parent Information
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'father_occupation' => 'required|string',
            'mother_occupation' => 'required|string',
            'father_contact' => 'required|string|min:10',
            'mother_contact' => 'required|string|min:10',
        ]);
        $user = Auth::user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Clinic not found for the user'
            ]);
        }


        try {
                DB::beginTransaction();
                $fieldsToUpdate = [];

                foreach (['name', 'category_id'] as $field) {
                    if ($doctor->isDirty($field)) {
                        $fieldsToUpdate[$field] = $validated[$field];
                    }
                }

                if (!empty($fieldsToUpdate)) {
                    $doctor->update($fieldsToUpdate);
                }
                
                
            $employeeFieldsToUpdate = [
                'image_profile' => $validated['image_profile'] 
                    ? $validated['image_profile']->store('image_profile') 
                    : 'image_profile/default.png',
                'image_signature' => $validated['image_signature'] 
                    ? $validated['image_signature']->store('image_signature') 
                    : 'image_signature/default.png',
                'branch' => $validated['branch'],
                'apc' => $validated['apc'],
                'mmc' => $validated['mmc'],
                'position' => $validated['position'],
                'staff_id' => $validated['staff_id'],
                'tenure' => $validated['tenure'],
                'basic_salary' => $validated['basic_salary'],
                'elaun' => $validated['elaun']
            ];

            foreach ($employeeFieldsToUpdate as $key => $value) {
                if ($doctor->employee->$key !== $value) {
                    $doctor->employee->$key = $value;
                }
            }

            $doctor->employee->save();

            $doctor->demographic()->delete();
            // Create related information
            $doctor->demographic()->create([
                'name' => $validated['name'],
                'nric' => $validated['nric'],
                'birth_date' => $validated['birth_date'],
                'place_of_birth' => $validated['place_of_birth'],
                'marital_status' => $validated['marital_status'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'address' => $validated['address'],
                'country' => $validated['country'],
                'postal_code' => $validated['postal_code'],
                'gender' => $validated['gender'],
            ]);

            $doctor->educational()->delete();
            $doctor->educational()->create([
                'graduated_from' => $validated['graduated_from'],
                'bachelor' => $validated['bachelor'],
                'graduation_year' => $validated['graduation_year'],
            ]);

            $doctor->employmentInformation()->delete();
            $doctor->contributionInfo()->create([
                'kwsp_number' => $validated['kwsp_number'],
                'kwsp_amount' => $validated['kwsp_amount'],
                'perkeso_number' => $validated['perkeso_number'],
                'perkeso_amount' => $validated['perkeso_amount'],
                'tax_number' => $validated['tax_number'],
                'tax_amount' => $validated['tax_amount'],
            ]);

            $doctor->emergencyContact()->delete();
            $doctor->emergencyContact()->create([
                'name' => $validated['emergency_contact'],
                'relationship' => $validated['emergency_contact_relation'],
                'phone_number' => $validated['emergency_contact_number'],
            ]);
            if($doctor->spouseInformation)
            {
                $doctor->spouseInformation()->delete();
            }
            if(isset($validated['spouse_name']))
            {
                $doctor->spouseInformation()->create([
                    'name' => $validated['spouse_name'],
                    'contact' => $validated['spouse_phone'],
                    'occupation' => $validated['spouse_occupation'],
                ]);
            }
            if($doctor->childsInformation)
            {
                $doctor->childsInformation()->delete();
            }
            if (!empty($validated['childs'])) {
                foreach ($validated['childs'] as $child) {
                    $doctor->childsInformation()->create([
                        'name' => $child['name'],
                        'age' => $child['age'],
                    ]);
                }
            }

            $doctor->parentInformation()->delete();
            $doctor->parentInformation()->create([
                'father_name' => $validated['father_name'],
                'father_occupation' => $validated['father_occupation'],
                'mother_name' => $validated['mother_name'],
                'mother_occupation' => $validated['mother_occupation'],
                'father_contact' => $validated['father_contact'],
                'mother_contact' => $validated['mother_contact'],
            ]);

            $doctor->reference()->delete();
            $doctor->reference()->create([
                'name' => $validated['reference_name'],
                'company' => $validated['reference_company'],
                'number_phone' => $validated['reference_phone'],
                'position' => $validated['reference_position'],
                'email' => $validated['reference_email'],
            ]);

            $doctor->basicSkills()->delete();
            $doctor->basicSkills()->create([
                'languange_spoken' => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office' => $validated['microsoft_office_skill'],
                'others' => $validated['others_skill'],
            ]);

            
            $doctor->financialInformation()->delete();
            $doctor->financialInformation()->create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

            DB::commit(); // Ensure all changes are saved to the database

            return response()->json([
                'status' => 'success',
                'message' => 'Doctor created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create doctor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function doctors(Request $request)
    {
        $user = Auth::user();
        $clinic = $user->clinic;
        if(!$clinic)
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'user not found'
            ]);
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
            'category',])->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $doctors            
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
            'status' => 'success',
            'message' => 'Successfully fetch data',
            'data' => $doctor
        ], 200);
    }

}