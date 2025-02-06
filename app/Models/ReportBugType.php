<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportBugType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    public function reportBug()
    {
        return $this->hasMany(ReportBug::class);
    }
}
