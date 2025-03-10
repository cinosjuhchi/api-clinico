<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    public function pregnancyCategory()
    {
        return $this->belongsTo(PregnancyCategory::class, 'pregnancy_category_id');
    }

    public function records()
    {
        return $this->hasMany(MedicationRecord::class, 'medication_id');
    }
}
