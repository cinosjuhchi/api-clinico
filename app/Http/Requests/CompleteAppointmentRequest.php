<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CompleteAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'blood_pressure' => 'required|string',
                'pulse_rate' => 'required|numeric',
                'temperature' => 'required|numeric',
                'weight' => 'required|numeric',
                'height' => 'required|numeric',
                'sp02' => 'required|numeric',
                'pain_score' => 'required|numeric',
                'respiratory_rate' => 'required|numeric',
                'alert' => 'nullable|string',
                // History
                'patient_condition' => 'required|string',
                'consultation_note' => 'required|string',
                'examination' => 'nullable|string',
                'allergy' => 'nullable|string',
                // Diagnosis
                'diagnosis' => 'required|array',
                'diagnosis.*' => 'required|string',
                'plan' => 'required|string',
                // Treatment
                'investigations' => 'nullable|array',
                'investigations.*.investigation_type' => 'required|string',
                'investigations.*.remark' => 'nullable|string',
                'investigations.*.name' => 'required|string',
                'investigations.*.cost' => 'required|numeric',
                // Treatment
                'procedure' => 'nullable|array',
                'procedure.*.name' => 'required|string',
                'procedure.*.remark' => 'nullable|string',
                'procedure.*.cost' => 'required|numeric',

                'injection' => 'nullable|array',
                'injection.*.injection_id' => 'nullable|exists:injections,id',
                'injection.*.name' => 'required|string',
                'injection.*.price' => 'required|numeric',
                'injection.*.cost' => 'required|numeric',

                'medicine' => 'nullable|array',
                'medicine.*.medicine_id' => 'nullable|exists:medications,id',
                'medicine.*.name' => 'required|string',
                'medicine.*.unit' => 'required|string',
                'medicine.*.frequency' => 'nullable|string',
                'medicine.*.cost' => 'required|numeric',
                'medicine.*.medicine_qty' => 'nullable|integer',
                // Bill
                'total_cost' => 'required|numeric',
                'transaction_date' => 'required|date',
                'service_id' => 'required|exists:clinic_services,id',
                // Consultation Image
                'images' => 'array|nullable',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'documents' => 'array|nullable',
                'documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',
                'reports' => 'array|nullable',
                'reports.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',
                'certificate' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',

                // Risk Factor
                'risk_factors' => 'array|nullable',
                'risk_factors.*' => 'required|string|max:125',
                'follow_up_date' => 'nullable|string',
                'follow_up_remark' => 'nullable|string',
                'current_history' => 'nullable|string',
                // Timer
                'timer' => 'nullable|date_format:H:i:s',
                // Gestational Age
                'gestational_age' => 'nullable|array',
                'gestational_age.plus' => 'sometimes|required|integer|min:0',
                'gestational_age.para' => 'sometimes|required|integer|min:0',
                'gestational_age.gravida' => 'sometimes|required|integer|min:0',
                'gestational_age.menstruation_date' => 'sometimes|required|date',
                // Medical Record Allergies
                'medical_record_allergies' => 'nullable|array',
                'medical_record_allergies.*' => 'nullable|string'
        ];
    }
}
