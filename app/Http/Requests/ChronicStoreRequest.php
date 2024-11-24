<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChronicStoreRequest extends FormRequest
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
            'chronic_medical' => 'required|string|max:125',
            'father_chronic_medical' => 'nullable|string|max:125',
            'mother_chronic_medical' => 'nullable|string|max:125',
            'patient_id' => 'required|exists:patients,id',
        ];
    }
}
