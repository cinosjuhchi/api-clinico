<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatDoctorBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor' => 'required|exists:users,id',   
            'name' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'transaction_date' => 'required|date',
            'email' => 'required|email',
            'amount' => 'required|numeric',            
            'reference_1_label' => 'nullable|string',
            'reference_1' => 'nullable|string',
        ];
    }
}
