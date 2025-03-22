<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAdminRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'department' => 'required|string|max:125',
            'role' => 'required|in:admin,superadmin',
            'branch' => 'required|string',
            'position' => 'required|string',
            'mmc' => 'required|numeric',
            'apc' => 'required|string',
            'staff_id' => 'required|string|unique:employees,staff_id',
            'tenure' => 'required|string',
            'basic_salary' => 'required|numeric',
            'elaun' => 'required|numeric',
            'image_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Demographics
            'birth_date' => 'required|date',
            'place_of_birth' => 'required|string',
            'gender' => 'required|in:male,female',
            'marital_status' => 'required|string',
            'nric' => 'required|string|unique:staff_demographics,nric',
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
            'is_doctor' => 'required|in:true,false',
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
            'childs.*.occupation'         => 'required|string',
            'childs.*.contact'           => 'required|string',
            // Parent Information
            'father_name'                => 'required|string',
            'mother_name'                => 'required|string',
            'father_occupation'          => 'required|string',
            'mother_occupation'          => 'required|string',
            'father_contact'             => 'required|string|min:10',
            'mother_contact'             => 'required|string|min:10',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => "invalid data",
            'errors' => $validator->errors(),
        ], 422));
    }
}
