<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyContact extends Model
{
    use HasFactory;
    protected $table = 'emergency_contacts';
    protected $guarded = ['id'];
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function familyRelationship()
    {
        return $this->belongsTo(FamilyRelationship::class, 'relationship', 'id');
    }
}
