<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "clinic_id",
        "date_from",
        "date_to",
        "leave_type_id",
        "reason",
        "attachment",
        "status", // pending, approved, declined
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
