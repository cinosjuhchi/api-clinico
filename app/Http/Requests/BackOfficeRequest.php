<?php

namespace App\Http\Requests;

use App\Rules\EmailOrName;
use Illuminate\Foundation\Http\FormRequest;

class BackOfficeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'user' => ['required', 'max:125', new EmailOrName],
            'password' => 'required|string|min:3'
        ];
    }
}