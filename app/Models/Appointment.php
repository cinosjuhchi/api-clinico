<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Billing::class, 'appointment_id');
    }
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ClinicService::class, 'clinic_service_id');
    }
    
}
