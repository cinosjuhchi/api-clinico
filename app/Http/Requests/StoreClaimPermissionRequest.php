<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimPermissionRequest extends FormRequest
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
            'claim_item_id' => 'required|integer|exists:claim_items,id',
            'month' => 'required|numeric|min:1|max:12',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
