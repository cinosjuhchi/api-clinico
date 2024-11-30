<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\RoomResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicResource extends JsonResource
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
            'slug' => $this->slug,            
            'address' => $this->address,  
            'company' => $this->company,
            'ssm_number' => $this->ssm_number,
            'registration_number' => $this->registration_number,
            'referral_number' => $this->referral_number,
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'pendingAppointments' => AppointmentResource::collection($this->whenLoaded('pendingAppointments')),
            'completedAppointments' => AppointmentResource::collection($this->whenLoaded('completedAppointments')),
            'doctors' => DoctorResource::collection($this->whenLoaded('doctors')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),            
            'user' => new UserResource($this->whenLoaded('user')),
            'financial' => new ClinicFinancialResource($this->whenLoaded('financial'))
        ];
    }
}
