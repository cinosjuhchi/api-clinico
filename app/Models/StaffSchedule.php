<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'day', // 1 = monday, 2 = tuesday, etc...
        'start_work',
        'end_work',
    ];

    public function staff()
    {
        return $this->belongsTo(AdminClinico::class, 'admin_clinico_id');
    }
}
