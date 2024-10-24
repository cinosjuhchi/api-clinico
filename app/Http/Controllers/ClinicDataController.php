<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ClinicResource;
use App\Http\Requests\StoreDoctorClinicRequest;

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

            // Create doctor profile
            $newDoctor = $clinic->doctors()->create([
                'user_id' => $newUser->id,
                'category_id' => $validated['category_id'],
            ]);

            // Create related information
            $newDoctor->demographic()->create([
                'nric' => $validated['nric'],
                'birth_date' => $validated['birth_date'],
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
                'phone_number' => $validated['spouse_phone'],
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
                'phone_number' => $validated['reference_phone'],
                'position' => $validated['reference_position'],
                'email' => $validated['reference_email'],
            ]);

            $newDoctor->basicSkills()->create([
                'languange_spoken' => $validated['languange_spoken_skill'],
                'languange_written' => $validated['languange_written_skill'],
                'microsoft_office' => $validated['microsoft_office_skill'],
                'others' => $validated['others_skill'],
            ]);

            $newDoctor->employmentInformation()->create([
                'image_profile' => $validated['image_profile'] 
                    ? $validated['image_profile']->store('image_profile') 
                    : 'path/to/default_profile_image.jpg',
                'image_signature' => $validated['image_signature'] 
                    ? $validated['image_signature']->store('image_signature') 
                    : 'path/to/default_signature_image.jpg',
                'branch' => $validated['branch'],
                'mmc' => $validated['mmc'],
                'position' => $validated['position'],
                'staff_id' => $validated['staff_id'],
                'tenure' => $validated['tenure'],
                'basic_salary' => $validated['basic_salary'],
                'elaun' => $validated['elaun'],
            ]);

            $newDoctor->financialInformation()->create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
            ]);

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

}