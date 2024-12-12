<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportClinic extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');        
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
