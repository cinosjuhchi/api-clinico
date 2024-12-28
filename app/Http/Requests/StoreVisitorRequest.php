<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorRequest extends FormRequest
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
            'user_agent' => 'nullable|string',
            'browser' => 'nullable|string',
            'browser_version' => 'nullable|string',
            'device' => 'nullable|string', // Bisa: mobile, desktop, tablet
            'operating_system' => 'nullable|string',
            'page_url' => 'required|string',
            'referrer_url' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
        ];
    }
}
