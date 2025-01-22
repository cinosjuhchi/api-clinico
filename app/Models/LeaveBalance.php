<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_detail_id',
        'bal',
        'bal_bf',
        'burned',
        'taken',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveTypeDetail()
    {
        return $this->belongsTo(LeaveTypeDetail::class);
    }
}
