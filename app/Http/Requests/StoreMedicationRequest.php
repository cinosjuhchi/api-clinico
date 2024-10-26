<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationRequest extends FormRequest
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
            'name' => 'string|required|max:255|min:3',
            'price' => 'numeric|required',            
            'brand' => 'string|required|max:255|min:3',
            'pregnancy_category_id' => 'required|exists:pregnancy_categories,id',
            'sku_code' => 'string|required|max:255|min:3',
            'paediatric_dose' => 'integer|required',
            'unit' => 'string|required|max:255',
            'batch' => 'integer|required',
            'expired_date' => 'date|required',
            'total_amount' => 'integer|required',      
            'manufacture' => 'string|required',
            'for' => 'string|required|max:125|min:3',
            'supplier' => 'string|required|max:255|min:3',      
            'clinic_id' => 'required|exists:clinics,id'
        ];
    }
}
