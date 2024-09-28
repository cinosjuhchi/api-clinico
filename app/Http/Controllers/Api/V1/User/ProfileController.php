<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\User;
use App\Models\Patient;
use App\Service\CheckUser;
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
use App\Notifications\SetUpProfileNotification;

class ProfileController extends Controller
{
    protected $checkUser;

    public function __construct(CheckUser $checkUser)
    {
        $this->checkUser = $checkUser;
    }

    public function me(Request $request)
    {
        $id = Auth::user()->id;

        // Periksa apakah user ada menggunakan checkUserExist
        $exist = $this->checkUser->checkUserExist($id);
        if (!$exist) {
            return response()->json([
                'status' => 'Not Found', 
                'message' => 'User not found, refresh your browser!'
            ], 404);
        }

        // Ambil parameter 'patient' dari request (jika ada)
        $patientId = $request->input('patient');

        // Query untuk mendapatkan pasien dengan user_id yang sama
        $patientQuery = Patient::with([
            'user',
            'appointments.clinic',
            'family.patients',            
            'demographics',
            'chronics',
            'medications',
            'physicalExaminations',
            'occupation',
            'immunizations',
            'emergencyContact',
            'parentChronic',
            'allergy'
        ])->where('user_id', $id);

        // Jika ada parameter 'patient', ambil pasien berdasarkan ID tersebut
        if ($patientId) {
            $patient = $patientQuery->where('id', $patientId)->first();
            if (!$patient) {
                return response()->json([
                    'status' => 'Not Found', 
                    'message' => 'Patient not found with the given ID'
                ], 404);
            }
        } else {
            // Jika tidak ada parameter 'patient', ambil patient pertama
            $patient = $patientQuery->first();
            if (!$patient) {
                return response()->json([
                    'status' => 'Not Found', 
                    'message' => 'No patients found for this user'
                ], 404);
            }
        }

        // Kembalikan data patient
        return response()->json([
            'status' => 'success', 
            'message' => 'Success to get data', 
            'data' => $patient
        ], 200);
    }


    public function setDemographic(Request $request ,int $id)
    {        
        // Validasi input yang diterima
        $validated = $request->validate([
            'mrn' => 'nullable|string|max:255',
            'date_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'nric' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
        ]);
        // Ambil data demografi untuk user terkait
        $demographic = DemographicInformation::findOrNew($id);

        // Update field dengan data yang divalidasi
        $demographic->update($validated);
    
        return response()->json(['status' => 'success','message' => 'Update Successful'], 200);
    }

    public function setChronicHealth(Request $request, int $id)
    {
        // Validasi input yang diterima
        $validated = $request->validate([
            'chronic_medical' => 'nullable|string|max:255',
            'father_chronic_medical' => 'nullable|string|max:255',
            'mother_chronic_medical' => 'nullable|string|max:255',
        ]);

        // Ambil data ChronicHealthRecord atau lempar 404 jika tidak ditemukan
        $chronicHealthRecord = ChronicHealthRecord::findOrNew($id);

        // Update field dengan data yang divalidasi
        $chronicHealthRecord->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Update Successful'
        ], 200);
    }



    public function setPhysicalExamination(Request $request, int $id)
    {   
        // Validasi input yang diterima
        $validated = $request->validate([
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
        ]);

        // Ambil atau buat data PhysicalExamination untuk user terkait
        $physicalExamination = PhysicalExamination::findOrNew($id);

        // Update field hanya jika ada input yang valid
        $physicalExamination->update($validated);
        
        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setOccupationRecord(Request $request ,int $id)
    {        
        // Validasi input yang diterima
        $validated = $request->validate([
            'job_position' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'panel' => 'nullable|string|max:255',
        ]);
        // Ambil atau buat data OccupationRecord untuk user terkait
        $occupationRecord = OccupationRecord::findOrNew($id);

        // Update field hanya jika ada input yang valid
        $occupationRecord->update($validated);

        return response()->json(['message' => 'Update successful'], 200);
    }

    public function setEmergencyContact(Request $request, int $id)
    {        
        // Validasi input yang diterima
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'panel' => 'nullable|string|max:255',
        ]);
        // Ambil atau buat data EmergencyContact untuk user terkait
        $emergencyContact = EmergencyContact::findOrNew($id);

        // Update field hanya jika ada input yang valid
        $emergencyContact->update($validated);

        return response()->json(['message' => 'Update successful'], 200);
    }


    public function setMedicationRecord(Request $request, int $id)
    {        
        // Validasi input yang diterima
        $validated = $request->validate([
            'medicine' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'allergy' => 'nullable|string|max:255',
        ]);
        // Ambil atau buat data MedicationRecord untuk user terkait
        $medicationRecord = MedicationRecord::findOrNew($id);

        // Update field hanya jika ada input yang valid
        $medicationRecord->update($validated);

        return response()->json(['message' => 'Update successful'], 200);
    }
    
    public function setImmunizationRecord(Request $request, int $id)
    {        
        // Validasi input yang diterima
        $validated = $request->validate([
            'vaccine_received' => 'nullable|string|max:255',
            'date_administered' => 'nullable|date',
        ]);
        
        // Ambil atau buat data ImmunizationRecord untuk user terkait
        $immunizationRecord = ImmunizationRecord::findOrNew($id);

        // Update field hanya jika ada input yang valid
        $immunizationRecord->update($validated);

        return response()->json(['status' => 'Success', 'message' => 'Update successful'], 200);
    }
}
