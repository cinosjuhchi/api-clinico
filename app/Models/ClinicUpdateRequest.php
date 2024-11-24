<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicUpdateRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
