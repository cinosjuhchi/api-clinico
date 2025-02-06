<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportBug extends Model
{
    use HasFactory;

    protected $fillable = [
        "email",
        "note",
        "report_bug_type_id",
    ];

    public function reportBugType()
    {
        return $this->belongsTo(ReportBugType::class);
    }
}
