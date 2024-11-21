<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3',
            'name' => 'required|string',
            'phone_number' => 'required|string|min:10|unique:users',            
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
        ];

    }
}
