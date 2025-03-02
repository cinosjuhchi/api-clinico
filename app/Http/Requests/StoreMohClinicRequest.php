<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMohClinicRequest extends FormRequest
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
            'name' => 'required|string|max:255|min:3|unique:clinics,name',    
            'incharge_name' => 'required|string|max:255|min:3',    
            'state' => 'required|string',
            'address' => 'required|string',
            'incharge_phone_number' => 'required|string|min:10',
            'head_department' => 'required|string',
            'post_code' => 'required|integer',            
            'referral_number' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|min:10|unique:users',
        ];
    }
}
