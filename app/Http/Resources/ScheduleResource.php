<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'clinic_id' => $this->clinic_id,
            'start' => Carbon::parse($this->start_time)->format('H:i'), // Parse and format the time
            'end' => Carbon::parse($this->end_time)->format('H:i'),     // Parse and format the time
        ];
    }
}
