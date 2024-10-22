<?php

namespace App\Http\Resources;

use Database\Seeders\ProcedureRecordSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MedicalRecordResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'clinic' => new ClinicResource($this->whenLoaded('clinic')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'patient_condition' => $this->patient_condition,
            'diagnosis' => $this->diagnosis,
            'procedure' => ProcedureRecordResource::collection($this->whenLoaded('procedureRecords')),
            'injection' => InjectionRecordResource::collection($this->whenLoaded('injectionRecords')),
            'medication' => MedicationRecordResource::collection($this->whenLoaded('medicationRecords')),
            'consultation_note' => $this->consultation_note,
            'physical_examination' => $this->physical_examination,
            'blood_presure' => $this->blood_pressure,
            'spo2' => $this->sp02,
            'temperature' => $this->temperature
        ];
    }
}
