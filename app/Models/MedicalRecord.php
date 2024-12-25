<?php

namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RiskFactor;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\ServiceRecord;
use App\Models\DiagnosisRecord;
use App\Models\InjectionRecord;
use App\Models\ProcedureRecord;
use App\Models\MedicationRecord;
use App\Models\InvestigationRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'medical_record_id');
    }

    public function riskFactors()
    {
        return $this->hasMany(RiskFactor::class, 'medical_record_id');
    }

    public function injectionRecords()
    {
        return $this->hasMany(InjectionRecord::class, 'medical_record_id');
    }

    public function procedureRecords()
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

    public function clinicService()
    {
        return $this->belongsTo(ClinicService::class, 'clinic_service_id');
    }

    public function serviceRecord()
    {
        return $this->hasOne(ServiceRecord::class, 'medical_record_id');
    }
    
    public function investigationRecord()
    {
        return $this->hasMany(InvestigationRecord::class, 'medical_record_id');
    }

    public function diagnosisRecord()
    {
        return $this->hasMany(DiagnosisRecord::class, 'medical_record_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
