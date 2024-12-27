<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user' => new UserResource($this->whenLoaded('user')),
            'demographic' => new StaffDemographicResource($this->whenLoaded('demographic')),
            'educational' => new StaffEducationalResource($this->whenLoaded('educational')),
            'reference' => new StaffReferenceResource($this->whenLoaded('reference')),
            'employmentInformation' => new EmploymentInformationResource($this->whenLoaded('employmentInformation')),
            'basicSkills' => new StaffBasicSkillResource($this->whenLoaded('basicSkills')),
            'contributionInfo' => new StaffContributionInfoResource($this->whenLoaded('contributionInfo')),
            'emergencyContact' => new StaffEmergencyContactResource($this->whenLoaded('emergencyContact')),
            'spouseInformation' => new StaffSpouseInformationResource($this->whenLoaded('spouseInformation')),
            'childsInformation' => StaffChildInformationResource::collection($this->whenLoaded('childsInformation')),
            'parentInformation' => new StaffParentInformationResource($this->whenLoaded('parentInformation')),
            'financialInformation' => new StaffFinancialInformationResource($this->whenLoaded('parentInformation')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'schedule' => DoctorScheduleResource::collection($this->whenLoaded('schedules')),
            'clinic' => new ClinicResource($this->whenLoaded('clinic')),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'pendingAppointments' => AppointmentResource::collection($this->whenLoaded('pendingAppointments')),
            'completedAppointments' => AppointmentResource::collection($this->whenLoaded('completedAppointments')),
            'consultationAppointments' => AppointmentResource::collection($this->whenLoaded('consultationAppointments')),
            'clinic_id' => $this->clinic_id,
            'room_id' => $this->room_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
