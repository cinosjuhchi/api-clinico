<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliated extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'month',
        'status', // paid, pending (default: pending)
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}
