<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'department' => 'required|string|max:125',
            'role' => 'required|in:admin,superadmin',
            'branch' => 'required|string',
            'position' => 'required|string',
            'mmc' => 'required|numeric',
            'apc' => 'required|string',
            'staff_id' => 'required|string',
            'tenure' => 'required|string',
            'basic_salary' => 'required|numeric',
            'elaun' => 'required|numeric',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_doctor' => 'required|in:true,false',

            // Demographics
            'birth_date' => 'required|date',
            'place_of_birth' => 'required|string',
            'gender' => 'required|in:male,female',
            'marital_status' => 'required|string',
            'nric' => 'required|string',
            'address' => 'required|string',
            'country' => 'required|string',
            'postal_code' => 'required|numeric',

            // Contributions
            'kwsp_number' => 'required|numeric',
            'kwsp_amount' => 'required|numeric',
            'perkeso_number' => 'required|numeric',
            'perkeso_amount' => 'required|numeric',
            'tax_number' => 'required|string',
            'tax_amount' => 'required|numeric',
            'eis' => 'required|numeric',

            // Financial
            'bank_name' => 'required|string',
            'account_number' => 'required|string',

            // Emergency Contact
            'emergency_contact' => 'required|string',
            'emergency_contact_number' => 'required|string|min:10',
            'emergency_contact_relation' => 'required|string',

            // Spouse Information
            'spouse_name' => 'nullable|string',
            'spouse_occupation' => 'nullable|string',
            'spouse_phone' => 'nullable|string',

            // Child Information
            'childs' => 'nullable|array',
            'childs.*.name' => 'required|string',
            'childs.*.occupation' => 'required|string',
            'childs.*.contact' => 'required|string',

            // Parent Information
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'father_occupation' => 'required|string',
            'mother_occupation' => 'required|string',
            'father_contact' => 'required|string|min:10',
            'mother_contact' => 'required|string|min:10',

            // Reference
            'reference_name'             => 'required|string',
            'reference_company'          => 'required|string',
            'reference_position'         => 'required|string',
            'reference_phone'            => 'required|string',
            'reference_email'            => 'required|email',

            // Educational Information
            'graduated_from' => 'required|string',
            'bachelor' => 'required|string',
            'graduation_year' => 'required|integer',

            // Basic Skill Information
            'languange_spoken_skill'     => 'nullable|string',
            'languange_written_skill'    => 'nullable|string',
            'microsoft_office_skill'     => 'nullable|string',
            'others_skill'               => 'nullable|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], 422));
    }
}
