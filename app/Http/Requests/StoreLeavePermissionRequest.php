<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLeavePermissionRequest extends FormRequest
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
            'date_from' => 'required|date|before_or_equal:date_to',
            'date_to' => 'required|date|after_or_equal:date_from',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'reason' => 'required|string|max:255',
            'attachment' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID wajib diisi.',
            'user_id.integer' => 'User ID harus berupa angka.',
            'user_id.exists' => 'User ID tidak valid.',
            'clinic_id.required' => 'Clinic ID wajib diisi.',
            'clinic_id.exists' => 'Clinic ID tidak valid.',
            'date_from.required' => 'Tanggal awal wajib diisi.',
            'date_from.before_or_equal' => 'Tanggal awal harus sebelum atau sama dengan tanggal akhir.',
            'date_to.required' => 'Tanggal akhir wajib diisi.',
            'date_to.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.',
            'leave_type_id.required' => 'Leave Type ID wajib diisi.',
            'leave_type_id.exists' => 'Leave Type ID tidak valid.',
            'reason.max' => 'Alasan tidak boleh lebih dari 255 karakter.',
            'attachment.mimes' => 'Lampiran harus berupa file dengan format jpg, png, atau pdf.',
            'attachment.max' => 'Lampiran tidak boleh lebih dari 2MB.',
            'status.in' => 'Status harus berupa salah satu dari pending, approved, atau declined.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => $validator->errors()
        ], 422));
    }
}
