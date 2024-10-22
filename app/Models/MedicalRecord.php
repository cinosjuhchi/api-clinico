<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'medical_record_id');
    }

    public function injectRecords()
    {
        return $this->hasMany(InjectionRecord::class, 'medical_record_id');
    }

    public function procedureRecord()
    {
        return $this->hasMany(ProcedureRecord::class, 'medical_record_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    
}
