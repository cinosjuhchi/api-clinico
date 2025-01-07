<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_type_id',
        'clinic_id',
        'year_ent'
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveBalance()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
