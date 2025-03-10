<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicInvoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(ClinicInvoiceItem::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
