<?php

namespace App\Helpers;

class ClinicHelper
{
    public static function nearbyClinic(float $latitude, float $longitude, int $radius = 5000)
    {
        $radiusInKm = $radius / 1000;

        return "
            6371 * acos(
                cos(radians($latitude)) * cos(radians(clinic_locations.latitude)) *
                cos(radians(clinic_locations.longitude) - radians($longitude)) +
                sin(radians($latitude)) * sin(radians(clinic_locations.latitude))
            ) <= $radiusInKm
        ";
    }
}
