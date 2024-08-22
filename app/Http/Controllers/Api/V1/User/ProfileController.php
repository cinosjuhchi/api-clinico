<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmergencyContact;
use App\Models\MedicationRecord;
use App\Models\OccupationRecord;
use App\Models\ImmunizationRecord;
use App\Models\ChronicHealthRecord;
use App\Models\PhysicalExamination;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DemographicInformation;
use App\Models\EmergencyContactInformation;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::with([
            'demographic',
            'chronic',
            'medication',
            'physical',
            'occupation',
            'immunization',
            'emergency'
        ])->find($id);                
        return response()->json(['message' => 'success to get data', "data" => $user], 200);
    }

    public function setDemographic(Request $request)
    {
        $validated = $request->validate([
            "mrn" => "nullable|string|max:255",
            "date_birth" => "nullable|date|before:today",
            "gender" => "nullable|string|in:male,female",
            "nric" => "nullable|string|max:255",
            "address" => "nullable|string|max:255",
            "country" => "nullable|string|max:255",
            "postal_code" => "nullable|numeric|digits_between:4,10",            
        ]);
    
        $id = Auth::user()->id;
    
        // Ambil data demografi untuk user terkait
        $demographic = DemographicInformation::where("user_id", $id)->first();
    
        if (!$demographic) {
            $new_demographic = new DemographicInformation();
            $new_demographic->user_id = $id;
            $new_demographic->save();
            return response()->json(['message' => 'Demographic information not found, refresh your browser!'], 404);
        }
    
        // Update field hanya jika ada input yang valid
        $demographic->mrn = $validated['mrn'] ?? $demographic->mrn;
        $demographic->date_birth = $validated['date_birth'] ?? $demographic->date_birth;
        $demographic->gender = $validated['gender'] ?? $demographic->gender;
        $demographic->nric = $validated['nric'] ?? $demographic->nric;
        $demographic->address = $validated['address'] ?? $demographic->address;
        $demographic->country = $validated['country'] ?? $demographic->country;
        $demographic->postal_code = $validated['postal_code'] ?? $demographic->postal_code;
    
        $demographic->save();
    
        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setChronicHealth(Request $request)
    {
        $validated = $request->validate([
            "chronic_medical" => "nullable|string|max:255",
            "father_chronic_medical" => "nullable|string|max:255",
            "mother_chronic_medical" => "nullable|string|max:255",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data ChronicHealthRecord untuk user terkait
        $chronicHealthRecord = ChronicHealthRecord::where("user_id", $id)->first();

        if (!$chronicHealthRecord) {
            $chronicHealthRecord = new ChronicHealthRecord();
            $chronicHealthRecord->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $chronicHealthRecord->chronic_medical = $validated['chronic_medical'] ?? $chronicHealthRecord->chronic_medical;
        $chronicHealthRecord->father_chronic_medical = $validated['father_chronic_medical'] ?? $chronicHealthRecord->father_chronic_medical;
        $chronicHealthRecord->mother_chronic_medical = $validated['mother_chronic_medical'] ?? $chronicHealthRecord->mother_chronic_medical;

        $chronicHealthRecord->save();

        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setPhysicalExamination(Request $request)
    {
        $validated = $request->validate([
            "height" => "nullable|numeric|min:0",
            "weight" => "nullable|numeric|min:0",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data PhysicalExamination untuk user terkait
        $physicalExamination = PhysicalExamination::where("user_id", $id)->first();

        if (!$physicalExamination) {
            $physicalExamination = new PhysicalExamination();
            $physicalExamination->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $physicalExamination->height = $validated['height'] ?? $physicalExamination->height;
        $physicalExamination->weight = $validated['weight'] ?? $physicalExamination->weight;

        $physicalExamination->save();

        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setOccupationRecord(Request $request)
    {
        $validated = $request->validate([
            "job_position" => "nullable|string|max:255",
            "company" => "nullable|string|max:255",
            "panel" => "nullable|string|max:100",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data OccupationRecord untuk user terkait
        $occupationRecord = OccupationRecord::where("user_id", $id)->first();

        if (!$occupationRecord) {
            $occupationRecord = new OccupationRecord();
            $occupationRecord->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $occupationRecord->job_position = $validated['job_position'] ?? $occupationRecord->job_position;
        $occupationRecord->company = $validated['company'] ?? $occupationRecord->company;
        $occupationRecord->panel = $validated['panel'] ?? $occupationRecord->panel;

        $occupationRecord->save();

        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setEmergencyContact(Request $request)
    {
        $validated = $request->validate([
            "name" => "nullable|string|max:125",
            "phone_number" => "nullable|string|unique:emergency_contacts,phone_number|max:255", // Perbaiki nama tabel di sini
            "panel" => "nullable|string|max:255",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data EmergencyContact untuk user terkait
        $emergencyContact = EmergencyContact::where("user_id", $id)->first();
        if (!$emergencyContact) {
            $emergencyContact = new EmergencyContact();
            $emergencyContact->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $emergencyContact->name = $validated['name'] ?? $emergencyContact->name;
        $emergencyContact->phone_number = $validated['phone_number'] ?? $emergencyContact->phone_number;
        $emergencyContact->panel = $validated['panel'] ?? $emergencyContact->panel;

        $emergencyContact->save();

        return response()->json(['message' => 'Update successful'], 200);
    }


    public function setMedicationRecord(Request $request)
    {
        $validated = $request->validate([
            "medicine" => "nullable|string|max:255",
            "frequency" => "nullable|string|max:255",
            "allergy" => "nullable|string|max:255",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data MedicationRecord untuk user terkait
        $medicationRecord = MedicationRecord::where("user_id", $id)->first();

        if (!$medicationRecord) {
            $medicationRecord = new MedicationRecord();
            $medicationRecord->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $medicationRecord->medicine = $validated['medicine'] ?? $medicationRecord->medicine;
        $medicationRecord->frequency = $validated['frequency'] ?? $medicationRecord->frequency;
        $medicationRecord->allergy = $validated['allergy'] ?? $medicationRecord->allergy;

        $medicationRecord->save();

        return response()->json(['message' => 'Update successful'], 200);
    }
    
    public function setImmunizationRecord(Request $request)
    {
        $validated = $request->validate([
            "vaccine_received" => "nullable|string|max:125",
            "date_administered" => "nullable|date|before:today",
        ]);

        $id = Auth::user()->id;

        // Ambil atau buat data ImmunizationRecord untuk user terkait
        $immunizationRecord = ImmunizationRecord::where("user_id", $id)->first();

        if (!$immunizationRecord) {
            $immunizationRecord = new ImmunizationRecord();
            $immunizationRecord->user_id = $id;
        }

        // Update field hanya jika ada input yang valid
        $immunizationRecord->vaccine_received = $validated['vaccine_received'] ?? $immunizationRecord->vaccine_received;
        $immunizationRecord->date_administered = $validated['date_administered'] ?? $immunizationRecord->date_administered;

        $immunizationRecord->save();

        return response()->json(['message' => 'Update successful'], 200);
    }
}
