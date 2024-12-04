<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "date", "clock_in", "clock_out", 'is_late', 'total_working_hours'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
