<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        if($user->role == "admin" || $user->role == "superadmin") {
            return true;
        }
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
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date',
            'addition' => 'required',
            'type' => 'required|in:cash,voucher,order,locum',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0|max_digits:8'
        ];
    }
}
