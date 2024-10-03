<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\RoomResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\ScheduleResource;
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
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'pendingAppointments' => AppointmentResource::collection($this->whenLoaded('pendingAppointments')),
            'completedAppointments' => AppointmentResource::collection($this->whenLoaded('completedAppointments')),
            'doctors' => DoctorResource::collection($this->whenLoaded('doctors')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),            
        ];
    }
}
