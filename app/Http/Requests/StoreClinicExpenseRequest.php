<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreClinicExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        if($user->role == "clinic") {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date',
            'addition' => 'required',
            'type' => 'required|in:cash,voucher,order,locum',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0|max_digits:8'
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
