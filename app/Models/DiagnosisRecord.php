<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosisRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
