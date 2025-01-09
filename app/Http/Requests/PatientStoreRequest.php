<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientStoreRequest extends FormRequest
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
            'address' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id', // Diubah menjadi nullable

            // Patient Contact
            'email' => 'required|string|email',
            'phone' => 'required|max:15',

            // Demographic information
            'mrn' => 'nullable|string|max:255',
            'date_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'nric' => 'nullable|string|max:255|unique:demographic_information,nric',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|integer',

            // Occupation record
            'job_position' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'panel' => 'required|string|max:255',

            // Emergency Contact Info
            'emergency_name' => 'required|string|max:255',
            'emergency_phone' => 'required|string|max:255',
            'emergency_relation' => 'required|string|max:255',

            // Chronic Health Records
            'chronic_medical' => 'required|string|max:255',

            // Parent Chronic
            'father_chronic_medical' => 'required|string',
            'mother_chronic_medical' => 'required|string',

            // Medication
            'medicine' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',

            // Allergies
            'allergies' => 'required|string|max:255',

            // Physical Exam
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',

            // Immunization Record
            'vaccine_received' => 'required|string|max:125',
            'date_administered' => 'required|date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
