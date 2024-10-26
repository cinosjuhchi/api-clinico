<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function investigation()
    {
        return $this->belongsTo(InvestigationClinic::class);
    }
}
