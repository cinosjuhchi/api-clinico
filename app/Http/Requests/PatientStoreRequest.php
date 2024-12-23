<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id', // Diubah menjadi nullable
            'family_id' => 'required|exists:families,id',
            'family_relationship_id' => 'required|exists:family_relationships,id',
            'payment_type' => 'required|in:CASH,ONLINE', // Validasi payment_type sebagai enum
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->payment_type === 'ONLINE' && !$this->user_id) {
                $validator->errors()->add('user_id', 'The user_id field is required when payment_type is ONLINE.');
            }
        });
    }
}
