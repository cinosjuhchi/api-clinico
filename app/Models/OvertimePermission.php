<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "clinic_id",
        "date",
        "start_time",
        "end_time",
        "reason",
        "attachment", // PDF atau PNG
        "status", // pending, approved, rejected
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
