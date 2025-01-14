<?php

namespace App\Helpers;

use App\Models\DemographicInformation;

class PatientCreateHelper {
    public static function createDemographics($patient, $validated)
    {
        $lastDemographicInfo = DemographicInformation::orderBy('id', 'desc')->first();
        $newMRN = 'MRN' . str_pad(($lastDemographicInfo ? ((int) substr($lastDemographicInfo->mrn, 3)) + 1 : 1), 7, '0', STR_PAD_LEFT);
        $patient->demographics()->create([
            "mrn" => $newMRN,
            "date_birth" => $validated["date_birth"],
            "gender" => $validated["gender"],
            "nric" => $validated["nric"],
            "address" => $validated["address"],
            "country" => $validated["country"],
            "postal_code" => $validated["postal_code"],
        ]);

    }


    public static function createContactInfo($patient, $validated)
    {
        $patient->patientContact()->create([
            "email" => $validated["email"],
            "phone" => $validated["phone"],
        ]);
    }


    public static function createOccupation($patient, $validated)
    {
        $patient->occupation()->create([
            "job_position" => $validated["job_position"],
            "company" => $validated["company"],
            "panel" => $validated["panel"],
        ]);

    }


    public static function createEmergencyContact($patient, $validated)
    {
        $patient->emergencyContact()->create([
            "name" => $validated["emergency_name"],
            "phone" => $validated["emergency_phone"],
            "relation" => $validated["emergency_relation"],
        ]);

    }


    public static function createChronicHealthRecord($patient, $validated)
    {
        foreach ($validated["chronic_medical"] as $chronic) {
            $patient->chronics()->create([
                "chronic_medical" => $chronic,
            ]);
        }
    }

    public static function createParentChronic($patient, $validated)
    {
        $patient->parentChronic()->create([
            "father_chronic_medical" => $validated["father_chronic_medical"],
            "mother_chronic_medical" => $validated["mother_chronic_medical"],
        ]);
    }


    public static function createMedication($patient, $validated)
    {
        foreach ($validated["medicines"] as $medicine) {
            $patient->medications()->create([
                "medicine" => $medicine["medicine"],
                "frequency" => $medicine["frequency"],
            ]);
        }
    }


    public static function createAllergy($patient, $validated)
    {
        $patient->allergy()->create([
            "name" => $validated["allergies"],
        ]);
    }

    public static function createPhysicalExamination($patient, $validated)
    {
        $patient->physicalExaminations()->create([
            "height" => $validated["height"],
            "weight" => $validated["weight"],
        ]);
    }

    public static function createImmunization($patient, $validated)
    {
        foreach ($validated["vaccines"] as $vaccine) {
            $patient->immunizations()->create([
                "vaccine_received" => $vaccine["vaccine_received"],
                "date_administered" => $vaccine["date_administered"],
            ]);
        }
    }
}
