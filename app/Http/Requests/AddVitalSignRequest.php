<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddVitalSignRequest extends FormRequest
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
            'height' => 'requiered|numeric',
            'weight' => 'requiered|numeric',
            'blood_pressure' => 'required|string',
            'sp02' => 'required|numeric',
            'temperature' => 'required|numeric',
            'pulse_rate' => 'required|numeric',
            'respiratory_rate' => 'required|numeric',
            
        ];
    }
}