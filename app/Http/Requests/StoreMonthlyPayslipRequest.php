<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMonthlyPayslipRequest extends FormRequest
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
            'user_id' => 'required|numeric|exists:users,id',
            'date' => 'required|date',
            'claim' => 'required|decimal:1,2',
            'overtime' => 'required|decimal:1,2',
            'hours' => 'required|decimal:1,2',
            'sale_incentives' => 'nullable|decimal:1,2',
            'kwsp' => 'required|decimal:1,2',
            'perkeso' => 'required|decimal:1,2',
            'tax' => 'required|decimal:1,2',
            'eis' => 'required|decimal:1,2',
            'basic_salary' => 'required|decimal:1,2',
            'clinic_id' => 'nullable|exists:clinics,id',
            'company' => 'required',
            'name' => 'required',
            'department' => 'required',
            'staff_id' => 'required',
            'nric' => 'required',
            'bank' => 'required',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => "invalid data",
            'errors' => $validator->errors(),
        ], 422));
    }
}
