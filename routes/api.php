<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmergencyContact;
use App\Models\ChronicHealthRecord;
use Illuminate\Support\Facades\Route;
use App\Models\DemographicInformation;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\ChronicController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PhysicalController;
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\ClinicDataController;
use App\Http\Controllers\DoctorDataController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\ParentChronicController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\PregnancyCategoryController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\FamilyRelationshipController;
use App\Http\Controllers\Api\V1\Doctor\IndexController;
use App\Http\Controllers\Api\V1\User\ProfileController;
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
            Route::get('/logout', [BackOfficeController::class, 'logout']);
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
                Route::get('/', [PatientNotificationController::class, 'getNotifications']);  // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [PatientNotificationController::class, 'markAsRead']);  // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [PatientNotificationController::class, 'markAllAsRead']);  // Tandai semua notifikasi sebagai sudah dibaca
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
            });
            Route::prefix('chronic')->group(function () {
                Route::post('/store', [ChronicController::class, 'store']);
            });
            Route::prefix('physical')->group(function () {
                Route::post('/store', [PhysicalController::class, 'store']);
            });
            Route::prefix('medicines')->group(function () {
                Route::post('store', [MedicationRecordController::class, 'store']);
            });
            Route::prefix('immunization')->group(function () {
                Route::post('/store', [ImmunizationController::class, 'store']);
            });
            Route::prefix('occupation')->group(function () {
                Route::post('/store', [OccupationController::class, 'store']);
            });
            Route::prefix('emergency')->group(function () {
                Route::post('/store', [EmergencyContactController::class, 'store']);
            });
            Route::prefix('parent-chronic')->group(function () {
                Route::post('/store', [ParentChronicController::class, 'store']);
            });
            Route::prefix('allergy')->group(function () {
                Route::post('/store', [AllergyController::class, 'store']);
            });

            // appointment route
            Route::prefix('appointment')->group(function () {
                Route::get('/', [AppointmentController::class, 'index']);
                Route::get('/my-appointment', [AppointmentController::class, 'myAppointment']);
                Route::get('/show/{slug}', [AppointmentController::class, 'show']);
                Route::get('/destroy/{slug}', [AppointmentController::class, 'destroy']);                
                Route::post('/store', [AppointmentController::class, 'store']);
                Route::put('/check-in/{appointment}', [AppointmentController::class, 'checkin']);
            });
        });
    });

    Route::prefix('doctor')->group(function () {
        Route::get('/{doctor}', [DoctorController::class, 'show']);
        Route::middleware(['auth:sanctum', 'abilities:doctor'])->group(function () {
            Route::prefix('me')->group( function () {
                Route::get('/logout-doctor', [DoctorAuthController::class, 'logout']);
                Route::get('/user', [DoctorProfileController::class, 'me']);
                Route::get('/doctor-patient', [DoctorProfileController::class, 'doctorPatient']);
            });              
            Route::prefix('consultation')->group(function () {                
                Route::put('/bill-payment/{appointment}', [ConsultationController::class, 'complete']);                
            });
        });
    });
    Route::prefix('clinic')->group(function () {
        Route::get('/', [ClinicController::class, 'index']);
        Route::get('/show/{slug}', [ClinicController::class, 'show']);
        Route::middleware(['auth:sanctum', 'abilities:clinic'])->group(function () {
            Route::prefix('me')->group( function () {
                Route::get('/logout-clinic', [ClinicAuthController::class, 'logout']);
                Route::get('/user', [ClinicDataController::class, 'me']);
                Route::get('/clinic-patient', [ClinicProfileController::class, 'clinicPatient']);                
            });      
            Route::prefix('medicines')->group(function () {
                Route::get('/', [MedicationController::class, 'index']);
                Route::get('/information', [MedicationController::class, 'information']);
                Route::post('/store', [MedicationController::class, 'store']);
                Route::put('/add-batch/{medication}', [MedicationController::class, 'addBatch']);
                Route::put('/update/{medication}', [MedicationController::class, 'update']);
                Route::delete('/delete/{medication}', [MedicationController::class, 'destroy']);
            });                            
            Route::prefix('procedure')->group(function () {
                Route::get('/', [ProcedureController::class, 'index']);
                Route::post('store', [ProcedureController::class, 'store']);
                Route::put('/update/{procedure}', [ProcedureController::class, 'update']);
                Route::delete('/delete/{procedure}', [ProcedureController::class, 'destroy']);
            });
            Route::prefix('injection')->group(function () {
                Route::get('/', [InjectionController::class, 'index']);
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
            });
        });
    });

    Route::middleware(['auth:sanctum', 'abilities:hasAccessResource'])->group(function () {
        Route::prefix('medicines')->group(function () {
            Route::get('/', [ClinicDataController::class, 'medicines']); 
         }); 
        Route::prefix('appointments')->group(function () {
            Route::prefix('doctor')->group(function () {
                Route::get('/show/{slug}', [DoctorDataController::class, 'showConsultation']);
                Route::get('/consultation-entry', [DoctorDataController::class, 'consultationEntry']);
                Route::delete('/cancel-appointment/{slug}', [AppointmentController::class, 'destroy']);                
            });            
        });
        Route::prefix('pregnancy-category')->group(function () {
            Route::get('/', [PregnancyCategoryController::class, 'index']);
            Route::get('/show/{pregnancyCategory}', [PregnancyCategoryController::class, 'show']);
        });

    });

    
    
});