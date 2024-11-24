<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreClinicUpdateRequestRequest extends FormRequest
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
        $user = Auth::user();        
        return [
            'name' => 'required|string',
            'bank_name' => 'required|string',
            'ac_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_detail' => 'required|string',
            'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($user->id)
            ],
        ];
    }
}
