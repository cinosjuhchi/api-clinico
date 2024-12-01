<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\ChronicController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PhysicalController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\ClinicDataController;
use App\Http\Controllers\DoctorDataController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\ClinicImageController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\ClinicServiceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\ParentChronicController;
use App\Http\Controllers\RequestClinicController;
use App\Http\Controllers\BackOfficeUserController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\OnlineEmployeeController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\BackOfficeDoctorController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\BackOfficeRevenueController;
use App\Http\Controllers\PregnancyCategoryController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\FamilyRelationshipController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\ClinicUpdateRequestController;
use App\Http\Controllers\InvestigationClinicController;
use App\Http\Controllers\PatientNotificationController;
use App\Http\Controllers\Api\V1\ClinicProfileController;
use App\Http\Controllers\Api\V1\DoctorProfileController;
use App\Http\Controllers\Api\V1\Auth\ClinicAuthController;
use App\Http\Controllers\Api\V1\Auth\DoctorAuthController;
use App\Http\Controllers\DemographicInformationController;

Route::prefix('v1')->group(function () {
    Route::prefix('back-office')->group(function () {
        Route::post('login', [BackOfficeController::class, 'login']);
        Route::middleware(['auth:sanctum', 'abilities:backOffice'])->group(function () {
            Route::prefix('bills')->group(function () {
                Route::get('/revenue', [BackOfficeRevenueController::class, 'index']);
                Route::get('/total-revenue-clinico', [BackOfficeRevenueController::class, 'totalRevenueTaxOnly']);
            });
            Route::get('/logout', [BackOfficeController::class, 'logout']);
            Route::prefix('user')->group(function () {
                Route::get('/total-user', [BackOfficeUserController::class, 'getTotal']);
                Route::get('/patient', [BackOfficeUserController::class, 'patients']);
            });
            Route::prefix('doctor')->group(function () {                
                Route::get('/', [BackOfficeDoctorController::class, 'index']);
            });
            Route::prefix('clinic')->group(function () {
                Route::get('/request-clinic', [RequestClinicController::class, 'index']);
                Route::delete('/delete/{clinic}', [RequestClinicController::class, 'destroy']);
                Route::put('/accept-request/{clinic}', [RequestClinicController::class, 'update']);
                Route::prefix('update-request')->group(function () {
                    Route::get('/', [ClinicUpdateRequestController::class, 'getPendingUpdates']);
                    Route::put('/proccess-update/{requestUpdate}', [ClinicUpdateRequestController::class, 'processUpdateRequest']);
                });
            });

        });
    });
    Route::prefix('doctor-category')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
    });
    Route::prefix('bill')->group(function () {
        Route::post('/store', [BillController::class, 'store']);
        Route::post('/callback', [BillController::class, 'callback']);
    });
    Route::prefix('guest')->group(function () {
        Route::get('/user', [UserController::class, 'index']);
        Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('/user-login', [AuthController::class, 'login']);
        Route::post('/doctor-login', [DoctorAuthController::class, 'login']);
        Route::post('/clinic-login', [ClinicAuthController::class, 'login']);
        Route::post('/user-register', [AuthController::class, 'store']);
        Route::post('/clinic-register', [ClinicAuthController::class, 'store']);
        Route::post('/contact-us', [ContactUsController::class, 'send']);
        Route::get('/email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum', 'abilities:user')->name('verification.resend');
    });
    Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
        Route::prefix('patient')->group(function () {
            Route::get('/user/{id}', [UserController::class, 'show']);
            Route::get('/logout-user', [AuthController::class, 'logout']);
            Route::post('/store', [PatientController::class, 'store']);
            Route::delete('/destroy/{patient}', [PatientController::class, 'destroy']);
            Route::prefix('notifications')->group(function () {
                Route::get('/', [PatientNotificationController::class, 'getNotifications']); // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [PatientNotificationController::class, 'markAsRead']); // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [PatientNotificationController::class, 'markAllAsRead']); // Tandai semua notifikasi sebagai sudah dibaca
            });

            Route::prefix('relationship')->group(function () {
                Route::get('/', [FamilyRelationshipController::class, 'index']);
            });

            Route::prefix('family')->group(function () {
                Route::get('/', [FamilyController::class, 'index']);
            });

            // profile route
            Route::prefix('me')->group(function () {
                Route::get('/user', [ProfileController::class, 'me']);
                Route::put('/update/demographic/{id}', [ProfileController::class, 'setDemographic']);
                Route::put('/update/chronical/{id}', [ProfileController::class, 'setChronicHealth']);
                Route::put('/update/physical/{id}', [ProfileController::class, 'setPhysicalExamination']);
                Route::put('/update/occupation/{id}', [ProfileController::class, 'setOccupationRecord']);
                Route::put('/update/emergency/{id}', [ProfileController::class, 'setEmergencyContact']);
                Route::put('/update/medication-record/{id}', [ProfileController::class, 'setMedicationRecord']);
                Route::put('/update/immunization/{id}', [ProfileController::class, 'setImmunizationRecord']);

            });

            Route::prefix('demographic')->group(function () {
                Route::post('/store', [DemographicInformationController::class, 'store']);
                Route::put('/update/{demographicInformation}', [DemographicInformationController::class, 'update']);
            });

            Route::prefix('medical-record')->group(function () {
                Route::get('/', [MedicalRecordController::class, 'index']);
                Route::get('/show/{medicalRecord}', [MedicalRecordController::class, 'show']);
            });

            Route::prefix('physical')->group(function () {
                Route::post('/store', [PhysicalController::class, 'store']);
                Route::put('/update/{physicalExamination}', [PhysicalController::class, 'update']);
            });
            Route::prefix('medicines')->group(function () {
                Route::post('/store', [MedicationRecordController::class, 'store']);
                Route::put('/update/{patient}', [MedicationRecordController::class, 'update']);
            });
            Route::prefix('immunization')->group(function () {
                Route::post('/store', [ImmunizationController::class, 'store']);
                Route::put('/update/{patient}', [ImmunizationController::class, 'update']);
            });
            Route::prefix('occupation')->group(function () {
                Route::post('/store', [OccupationController::class, 'store']);
                Route::put('/update/{occupationRecord}', [OccupationController::class, 'update']);
            });
            Route::prefix('emergency')->group(function () {
                Route::post('/store', [EmergencyContactController::class, 'store']);
                Route::put('/update/{emergencyContact}', [EmergencyContactController::class, 'update']);
            });
            Route::prefix('chronic')->group(function () {
                Route::post('/store', [ChronicController::class, 'store']);
                Route::put('/update/{patient}', [ChronicController::class, 'update']);
            });
            Route::prefix('parent-chronic')->group(function () {
                Route::post('/store', [ParentChronicController::class, 'store']);
                Route::put('/update/{parentChronic}', [ParentChronicController::class, 'update']);
            });
            Route::prefix('allergy')->group(function () {
                Route::post('/store', [AllergyController::class, 'store']);
                Route::put('/update/{allergy}', [AllergyController::class, 'update']);
            });

            // appointment route
            Route::prefix('appointment')->group(function () {
                Route::get('/', [AppointmentController::class, 'index']);
                Route::get('/show/{slug}', [AppointmentController::class, 'show']);
                Route::get('/destroy/{slug}', [AppointmentController::class, 'destroy']);
                Route::get('/waiting-number', [AppointmentController::class, 'waitingNumber']);
                Route::post('/store', [AppointmentController::class, 'store']);
                Route::put('/check-in/{appointment}', [AppointmentController::class, 'checkin']);
                Route::put('/take-medicine/{appointment}', [AppointmentController::class, 'takeMedicine']);
            });
            Route::prefix('bills')->group(function () {
                Route::get('/', [BillController::class, 'index']);
                Route::get('/show/{billing}', [BillController::class, 'show']);
            });
        });
    });

    Route::prefix('doctor')->group(function () {
        Route::get('/{doctor}', [DoctorController::class, 'show']);
        Route::middleware(['auth:sanctum', 'abilities:doctor'])->group(function () {
            Route::prefix('me')->group(function () {
                Route::get('/logout-doctor', [DoctorAuthController::class, 'logout']);
                Route::get('/user', [DoctorProfileController::class, 'me']);
                Route::get('/doctor-patient', [DoctorProfileController::class, 'doctorPatient']);
            });
            Route::prefix('consultation')->group(function () {
                Route::put('/complete/{appointment}', [ConsultationController::class, 'complete']);
                Route::put('/call-patient/{appointment}', [ConsultationController::class, 'callPatient']);
            });
        });
    });
    Route::prefix('staff')->group(function () {
        Route::get('/{staff}', [StaffAuthController::class, 'show']);
        Route::middleware(['auth:sanctum', 'abilities:staff'])->group(function () {
            Route::prefix('me')->group(function () {
                Route::get('/logout-staff', [StaffAuthController::class, 'logout']);
                Route::get('/user', [StaffAuthController::class, 'me']);
            });
            Route::prefix('consultation')->group(function () {
                Route::put('/complete/{appointment}', [ConsultationController::class, 'complete']);
                Route::put('/call-patient/{appointment}', [ConsultationController::class, 'callPatient']);
            });
        });
    });
    Route::prefix('clinic')->group(function () {
        Route::get('/', [ClinicController::class, 'index']);
        Route::get('/show/{slug}', [ClinicController::class, 'show']);
        Route::middleware(['auth:sanctum', 'abilities:clinic'])->group(function () {
            Route::prefix('me')->group(function () {
                Route::get('/logout-clinic', [ClinicAuthController::class, 'logout']);
                Route::get('/user', [ClinicDataController::class, 'me']);
                Route::post('/update-profile-request', [ClinicUpdateRequestController::class, 'requestUpdate']);
                Route::get('/clinic-patient', [ClinicProfileController::class, 'clinicPatient']);                
                Route::post('/{clinic}/images', [ClinicImageController::class, 'store']);
                Route::post('/store-profile-image', [ClinicImageController::class, 'storeProfile']);
            });
        });
        Route::middleware(['auth:sanctum', 'abilities:hasAccessResource'])->group(function () {
            Route::get('/clinic-information', [ClinicController::class, 'clinicInformation']);
            Route::prefix('medicines')->group(function () {
                Route::get('/', [MedicationController::class, 'index']);
                Route::get('/doctor-resource', [MedicationController::class, 'doctorResource']);
                Route::get('/information', [MedicationController::class, 'information']);
                Route::post('/store', [MedicationController::class, 'store']);
                Route::put('/add-batch/{medication}', [MedicationController::class, 'addBatch']);
                Route::put('/update/{medication}', [MedicationController::class, 'update']);
                Route::delete('/delete/{medication}', [MedicationController::class, 'destroy']);
            });
            Route::prefix('procedure')->group(function () {
                Route::get('/', [ProcedureController::class, 'index']);
                Route::get('/doctor-resource', [ProcedureController::class, 'doctorResource']);
                Route::post('store', [ProcedureController::class, 'store']);
                Route::put('/update/{procedure}', [ProcedureController::class, 'update']);
                Route::delete('/delete/{procedure}', [ProcedureController::class, 'destroy']);
            });
            Route::prefix('injection')->group(function () {
                Route::get('/', [InjectionController::class, 'index']);
                Route::get('/doctor-resource', [InjectionController::class, 'doctorResource']);
                Route::post('/store', [InjectionController::class, 'store']);
                Route::put('/update/{injection}', [InjectionController::class, 'update']);
                Route::delete('/delete/{injection}', [InjectionController::class, 'destroy']);
                Route::put('/add-batch/{injection}', [InjectionController::class, 'addBatch']);
            });
            Route::prefix('doctor')->group(function () {
                Route::get('/', [ClinicDataController::class, 'doctors']);
                Route::get('/show/{doctor}', [ClinicDataController::class, 'showDoctor']);
                Route::post('/store', [ClinicDataController::class, 'storeDoctor']);
                Route::put('/update/{doctor}', [ClinicDataController::class, 'updateDoctor']);
                Route::delete('/delete/{doctor}', [ClinicDataController::class, 'destroyDoctor']);
                Route::prefix('schedule')->group(function () {
                    Route::get('/', [DoctorScheduleController::class, 'index']);
                    Route::post('/store', [DoctorScheduleController::class, 'store']);
                    Route::put('/update/{doctorSchedule}', [DoctorScheduleController::class, 'update']);
                    Route::delete('/delete/{doctorSchedule}', [DoctorScheduleController::class, 'destroy']);
                });
            });
            Route::prefix('staff')->group(function () {
                Route::get('/', [ClinicDataController::class, 'staffs']);
                Route::get('/show/{staff}', [ClinicDataController::class, 'showStaff']);
                Route::post('/store', [ClinicDataController::class, 'storeStaff']);
            });
            Route::prefix('rooms')->group(function () {
                Route::get('/resource', [RoomController::class, 'roomResource']);
                Route::get('/', [RoomController::class, 'index']);
                Route::post('/store', [RoomController::class, 'store']);
                Route::put('/update/{room}', [RoomController::class, 'update']);
                Route::put('/delete/{room}', [RoomController::class, 'destroy']);
            });
            Route::prefix('employee')->group(function () {
                Route::delete('/delete/{employee}', [EmployeeController::class, 'destroy']);
            });
            Route::prefix('services')->group(function () {
                Route::get('/', [ClinicServiceController::class, 'index']);
                Route::get('/doctor-resource', [ClinicServiceController::class, 'doctorResource']);
                Route::post('/store', [ClinicServiceController::class, 'store']);
                Route::put('/update/{clinicService}', [ClinicServiceController::class, 'update']);
                Route::delete('/delete/{clinicService}', [ClinicServiceController::class, 'destroy']);
            });
            Route::prefix('investigations')->group(function () {
                Route::get('/', [InvestigationClinicController::class, 'index']);
                Route::get('/doctor-resource', [InvestigationClinicController::class, 'doctorResource']);
                Route::post('/store', [InvestigationClinicController::class, 'store']);
                Route::put('/update/{investigationClinic}', [InvestigationClinicController::class, 'update']);
                Route::delete('/delete/{investigationClinic}', [InvestigationClinicController::class, 'destroy']);
            });
            Route::prefix('appointments')->group(function () {
                Route::put('/patient/{patient}/add-vital-sign', [PatientController::class, 'addVitalSign']);
                Route::put('/patient/{appointment}/add-status', [PatientController::class, 'addStatus']);
                Route::get('/patient', [PatientController::class, 'index']);
                Route::get('/today-patient', [ClinicProfileController::class, 'clinicPatient']);
                Route::get('/booking-patient', [PatientController::class, 'bookingPatient']);
                Route::get('/waiting-patient', [PatientController::class, 'waitingPatient']);
                Route::get('/completed-patient', [PatientController::class, 'completedPatient']);
                
            });
        });

    });

    Route::middleware(['auth:sanctum', 'abilities:hasAccessResource'])->group(function () {
        Route::prefix('medicines')->group(function () {
            Route::get('/', [ClinicDataController::class, 'medicines']);
        });
        Route::prefix('users')->group(function () {
            Route::get('/online-list', [OnlineEmployeeController::class, 'index']);
        });
        Route::prefix('bills')->group(function () {
            Route::get('/clinic-revenue', [BillController::class, 'clinicRevenue']);
            Route::get('/clinic-total-revenue-month', [BillController::class, 'clinicTotalRevenue']);
            Route::get('/clinic-total-revenue-daily', [BillController::class, 'clinicDailyTotalRevenue']);
            Route::get('/clinic-total-revenue-doctor', [BillController::class, 'clinicTotalRevenueByDoctor']);
        });
        Route::prefix('appointments')->group(function () {
            Route::prefix('doctor')->group(function () {
                Route::get('/show/{slug}', [DoctorDataController::class, 'showConsultation']);
                Route::get('/consultation-entry', [DoctorDataController::class, 'consultationEntry']);
                Route::get('/completed-entry', [DoctorDataController::class, 'completedEntry']);
                Route::delete('/cancel-appointment/{slug}', [AppointmentController::class, 'destroy']);                
            });

            Route::get('/dispensary', [ConsultationController::class, 'dispensary']);
            Route::get('/consultation-entry', [ConsultationController::class, 'consultationEntry']);
            Route::put('/take-medicine/{appointment}', [ConsultationController::class, 'takeMedicine']);
        });
        Route::prefix('diagnosis')->group(function () {
            Route::get('/', [DiagnosisController::class, 'index']);
        });
        Route::prefix('pregnancy-category')->group(function () {
            Route::get('/', [PregnancyCategoryController::class, 'index']);
            Route::get('/show/{pregnancyCategory}', [PregnancyCategoryController::class, 'show']);
        });
        Route::prefix('rooms')->group(function () {
            Route::get('/resource', [RoomController::class, 'roomResource']);
            Route::get('/', [RoomController::class, 'index']);
        });

    });
});
