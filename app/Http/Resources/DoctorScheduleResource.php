<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
class DoctorScheduleResource extends JsonResource
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
            'start' => Carbon::parse($this->start_time)->format('H:i'), // Parse and format the time
            'end' => Carbon::parse($this->end_time)->format('H:i'), 
            'day' => $this->day,            
        ];
    }
}
