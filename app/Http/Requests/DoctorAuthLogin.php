<?php

namespace App\Http\Requests;

use App\Rules\EmailOrName;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DoctorAuthLogin extends FormRequest
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
            'user' => ['required', 'max:125', new EmailOrName],
            'password' => 'required|string|min:3'
        ];
    }

    public function messages()
    {
        return [
            'user.required' => 'Tolong isi antara Nama atau Email anda.',            
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi harus minimal 3 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'errors' => $validator->errors()
            ], 422));
    }

}
