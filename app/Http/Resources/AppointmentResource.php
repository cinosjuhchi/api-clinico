<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'visit_purpose' => $this->visit_purpose,
            'current_condition' => $this->current_condition,
            'waiting_number' => $this->waiting_number,
            'appointment_date' => $this->appointment_date,
        ];
    }
}
