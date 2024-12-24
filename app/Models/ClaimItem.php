<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
    ];

    public $timestamps = false;

    public function claimPermissions()
    {
        return $this->belongsToMany(ClaimPermission::class);
    }
}
