<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOvertimePermissionRequest extends FormRequest
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
    public function rules()
    {
        return [
            "date" => ["required", "date", "after_or_equal:today"],
            "start_time" => ["required", "date_format:H:i"],
            "end_time" => ["required", "date_format:H:i", "after:start_time"],
            "reason" => ["required", "string", "max:255"],
            "attachment" => ["required", "file", "mimes:pdf,png,jpg,jpeg", "max:2048"],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => "invalid data",
            'errors' => $validator->errors(),
        ], 422));
    }
}
