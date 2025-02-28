<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportInformationRequest extends FormRequest
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
            'from_date' => 'required|date|before:to_date',
            'to_date' => 'required|date|after:from_date'
        ];
    }
}
