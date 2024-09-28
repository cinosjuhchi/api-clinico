<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FamilyRelationshipResource;
use App\Http\Resources\DemographicInformationResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PatientResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'relationship' => new FamilyRelationshipResource($this->whenLoaded('familyRelationship')),
            'demographics' => new DemographicInformationResource($this->whenLoaded('demographics')),
        ];
    }
}
