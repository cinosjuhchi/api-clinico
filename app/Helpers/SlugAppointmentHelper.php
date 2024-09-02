<?php

namespace App\Helpers;

use App\Models\Appointment;
use Illuminate\Support\Str;

class SlugAppointmentHelper
{
    /**
     * Create a new class instance.
     */
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $slugExists = Appointment::where('slug', $slug)->exists();
        $counter = 1;
        while ($slugExists) {
            $newSlug = Str::slug($title) . '-' . $counter;
            $slugExists = Appointment::where('slug', $newSlug)->exists();
            $counter++;
        }
        return $slugExists ? $newSlug : $slug;
    }
}
