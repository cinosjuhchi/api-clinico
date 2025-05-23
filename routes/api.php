<?php

use App\Models\ReportClinic;
use App\Models\MessageClinico;
use App\Models\OnlineConsultation;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\ChronicController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\BoReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PhysicalController;
use App\Http\Controllers\BoExpenseController;
use App\Http\Controllers\BoInvoiceController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\MohClinicController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\ChatDoctorController;
use App\Http\Controllers\ClinicDataController;
use App\Http\Controllers\DoctorDataController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\ClinicImageController;
use App\Http\Controllers\PanelClinicController;
use App\Http\Controllers\TeleconsultController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\ReportClinicController;
use App\Http\Controllers\BoInvoiceItemController;
use App\Http\Controllers\ClinicServiceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\ParentChronicController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RequestClinicController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\BackOfficeUserController;
use App\Http\Controllers\ChatDoctorBillController;
use App\Http\Controllers\ClinicLocationController;
use App\Http\Controllers\ClinicScheduleController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\MessageClinicoController;
use App\Http\Controllers\OnlineEmployeeController;
use App\Http\Controllers\Api\V1\ClaimItemController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\ReportBugController;
use App\Http\Controllers\Api\V1\StatisticController;
use App\Http\Controllers\BackOfficeDoctorController;
use App\Http\Controllers\ClinicSettlementController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\MedicationRecordController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\Api\V1\AffiliatedController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\BackOfficeRevenueController;
use App\Http\Controllers\PregnancyCategoryController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\TopEmployeeController;
use App\Http\Controllers\ClinicNotificationController;
use App\Http\Controllers\FamilyRelationshipController;
use App\Http\Controllers\OnlineConsultationController;
use App\Http\Controllers\Api\V1\ClinicReportController;
use App\Http\Controllers\Api\V1\LeaveBalanceController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\ClinicUpdateRequestController;
use App\Http\Controllers\InvestigationClinicController;
use App\Http\Controllers\PatientNotificationController;
use App\Http\Controllers\Api\V1\ClinicExpenseController;
use App\Http\Controllers\Api\V1\ClinicInvoiceController;
use App\Http\Controllers\Api\V1\ClinicProfileController;
use App\Http\Controllers\Api\V1\DoctorProfileController;
use App\Http\Controllers\Api\V1\ReportBugTypeController;
use App\Http\Controllers\Api\V1\StaffScheduleController;
use App\Http\Controllers\ConsultationDocumentController;
use App\Http\Controllers\Api\V1\MonthlyPayslipController;
use App\Http\Controllers\Api\V1\Auth\ClinicAuthController;
use App\Http\Controllers\Api\V1\Auth\DoctorAuthController;
use App\Http\Controllers\Api\V1\ClaimPermissionController;
use App\Http\Controllers\Api\V1\LeavePermissionController;
use App\Http\Controllers\Api\V1\LeaveTypeDetailController;
use App\Http\Controllers\BackOfficeNotificationController;
use App\Http\Controllers\DemographicInformationController;
use App\Http\Controllers\Api\V1\ClinicInvoiceItemController;
use App\Http\Controllers\Api\V1\OvertimePermissionController;
use App\Http\Controllers\Api\V1\StaffClinicScheduleController;

Route::prefix('v1')->group(function () {
    Route::prefix('back-office')->group(function () {
        Route::post('login', [BackOfficeController::class, 'login']);
        Route::middleware(['auth:sanctum', 'abilities:backOffice'])->group(function () {
            Route::prefix('web-push')->group(function () {
                Route::post('/save-notification', [PushNotificationController::class, 'saveSubscription']);
            });
            Route::prefix('notifications')->group(function () {
                Route::get('/all', [BackOfficeNotificationController::class, 'getNotifications']); // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [BackOfficeNotificationController::class, 'markAsRead']); // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [BackOfficeNotificationController::class, 'markAllAsRead']); // Tandai semua notifikasi sebagai sudah dibaca
            });
            Route::get('/me', [BackOfficeController::class, 'me']);
            Route::get('/top-employee', [TopEmployeeController::class, 'index']);
            Route::prefix('visit')->group(function () {
                Route::get('/get-total-visit', [VisitorController::class, 'getTotalViewPage']);
            });
            Route::prefix('bills')->group(function () {
                Route::prefix('settlements')->group(function () {
                    Route::get('/', [ClinicSettlementController::class, 'index']);
                    Route::get('/show/{clinicSettlement}', [ClinicSettlementController::class, 'show']);
                    Route::put('/completed/{clinicSettlement}', [ClinicSettlementController::class, 'completed']);
                    Route::delete('/delete/{clinicSettlement}', [ClinicSettlementController::class, 'destroy']);
                    Route::post('/store', [ClinicSettlementController::class, 'store']);

                });
                Route::get('/revenue', [BackOfficeRevenueController::class, 'index']);
                Route::get('/total-revenue', [BackOfficeRevenueController::class, 'totalRevenue']);
                Route::get('/total-revenue/month', [BackOfficeRevenueController::class, 'getRevenueByDate']);
                Route::get('/total-revenue-clinico', [BackOfficeRevenueController::class, 'totalRevenueTaxOnly']);
                Route::get('/total-statistic-year', [BackOfficeRevenueController::class, 'totalRevenueGroupedByMonth']);
            });
            Route::get('/logout', [BackOfficeController::class, 'logout']);
            Route::prefix('user')->group(function () {
                Route::get('/total-user', [BackOfficeUserController::class, 'getTotal']);
                Route::get('/patient', [BackOfficeUserController::class, 'patients']);
            });
            Route::prefix('doctor')->group(function () {
                Route::get('/', [BackOfficeDoctorController::class, 'index']);
            });
            Route::prefix('staff')->group(function () {
                Route::get('/online-list', [OnlineEmployeeController::class, 'onlineAdmin']);
                Route::get('/', [BackOfficeController::class, 'index']);
                Route::get('/schedules', [StaffScheduleController::class, 'index']);
                Route::get('/schedules/{staff}', [StaffScheduleController::class, 'show']);
                Route::post('/schedules', [StaffScheduleController::class, 'store']);
                Route::put('/schedules/{staff}', [StaffScheduleController::class, 'update']);
                Route::delete('/schedules/{staff}', [StaffScheduleController::class, 'destroy']);
                Route::post('/store', [BackOfficeController::class, 'storeStaff']);
                Route::get('/{id}', [BackOfficeController::class, 'show']);
                Route::put('/{admin}', [BackOfficeController::class, 'updateStaff']);
                Route::delete('/{admin}', [BackOfficeController::class, 'deleteStaff']);
            });
            Route::prefix('clinic')->group(function () {
                Route::get('/', [ClinicController::class, 'clinics']);
                Route::get('/request-clinic', [RequestClinicController::class, 'index']);
                Route::delete('/delete/{clinic}', [RequestClinicController::class, 'destroy']);
                Route::put('/accept-request/{clinic}', [RequestClinicController::class, 'update']);
                Route::post('/store', [RequestClinicController::class, 'store']);
                Route::prefix('moh')->group(function () {
                    Route::get('/', [MohClinicController::class, 'index']);
                    Route::post('/store', [RequestClinicController::class, 'storeMoh']);
                });
                Route::prefix('update-request')->group(function () {
                    Route::get('/', [ClinicUpdateRequestController::class, 'getPendingUpdates']);
                    Route::put('/proccess-update/{requestUpdate}', [ClinicUpdateRequestController::class, 'processUpdateRequest']);
                });
                Route::prefix('report')->group(function () {
                    Route::get('/pending', [ReportClinicController::class, 'getPendingReport']);
                    Route::get('/complete', [ReportClinicController::class, 'getCompleteReport']);
                    Route::put('/process-update/{reportClinic}', [ReportClinicController::class, 'approved']);
                });
            });

            Route::prefix('growth')->group(function() {
                Route::get('/', [BackofficeController::class, 'growthOfRegistration']);
            });

            Route::prefix('teleconsult')->group(function () {
                Route::get('/', [TeleconsultController::class, 'index']);
                Route::get('/get-message/{onlineConsultation}', [TeleconsultController::class, 'show']);
                Route::post('/send-message/{onlineConsultation}', [TeleconsultController::class, 'store']);
            });

            Route::prefix('invoice')->group(function () {
                Route::get('/', [BoInvoiceController::class, 'index']);
                Route::post('/store', [BoInvoiceController::class, 'store']);
                Route::get('/confirm/{boInvoice}', [BoInvoiceController::class, 'completed']);
                Route::get('/show/{boInvoice}', [BoInvoiceController::class, 'show']);
                Route::delete('/delete/{boInvoice}', [BoInvoiceController::class,'destroy']);
                Route::prefix('items')->group(function () {
                    Route::delete('/delete/{boInvoiceItem}', [BoInvoiceItemController::class, 'destroy']);
                });
            });

            Route::prefix('expense')->group(function () {
                Route::get('/', [BoExpenseController::class, 'index']);
                Route::post('/store', [BoExpenseController::class, 'store']);
                Route::get('/confirm/{boExpense}', [BoExpenseController::class, 'completed']);
                Route::get('/show/{boExpense}', [BoExpenseController::class, 'show']);
                Route::delete('/delete/{boExpense}', [BoExpenseController::class, 'destroy']);
            });

            Route::prefix('report-information')->group(function () {
                Route::get('/total-sales', [BoReportController::class, 'totalSales']);
                Route::get('/invoices', [BoReportController::class, 'invoices']);
                Route::get('/settlements', [BoReportController::class, 'settlements']);
                Route::get('/total-cash', [BoReportController::class, 'totalCash']);
                Route::get('/total-orders', [BoReportController::class, 'totalOrders']);
                Route::get('/total-vouchers', [BoReportController::class, 'totalVouchers']);
                Route::get('/total-locums', [BoReportController::class, 'totalLocums']);
            });

            Route::post('/affiliated/{referralId}/month/{month}', [AffiliatedController::class, 'update']);
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
        Route::post('/send-forgot-password', [PasswordResetController::class, 'sendResetLink']);
        Route::get('/email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum', 'abilities:user')->name('verification.resend');
        Route::get('/validate/token/change-password/{token}', [PasswordResetController::class, 'validateResetToken']);
        Route::post('/change-password/store', [PasswordResetController::class, 'store']);
        Route::post('/add-visit', [VisitorController::class, 'store']);
    });
    Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
        Route::prefix('patient')->group(function () {
            Route::prefix('web-push')->group(function () {
                Route::post('/save-notification', [PushNotificationController::class, 'saveSubscription']);
            });
            Route::prefix('panel')->group(function () {
                Route::get('/resources', [PanelClinicController::class, 'patientResource']);
            });
            Route::post('/store', [FamilyController::class, 'store']);
            Route::get('/user/{id}', [UserController::class, 'show']);
            Route::get('/logout-user', [AuthController::class, 'logout']);
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

            Route::prefix('report')->group(function () {
                Route::post('/store', [ReportClinicController::class, 'store']);
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
                Route::prefix('document')->group(function () {
                    Route::get('/download-file', [ConsultationDocumentController::class, 'download']);
                });
            });

            Route::prefix('physical')->group(function () {
                Route::post('/store', [PhysicalController::class, 'store']);
                Route::put('/update/{physicalExamination}', [PhysicalController::class, 'update']);
            });
            Route::prefix('medicines')->group(function () {
                Route::post('/store', [MedicationRecordController::class, 'store']);
                Route::put('/update/{patient}', [MedicationRecordController::class, 'update']);
                Route::delete('/delete/{medicationRecord}', [MedicationRecordController::class, 'destroy']);

            });
            Route::prefix('immunization')->group(function () {
                Route::post('/store', [ImmunizationController::class, 'store']);
                Route::put('/update/{patient}', [ImmunizationController::class, 'update']);
                Route::delete('/delete/{immunizationRecord}', [ImmunizationController::class, 'destroy']);
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
                Route::delete('/delete/{chronicHealthRecord}', [ChronicController::class, 'destroy']);

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
            // Doctor Chat
            Route::prefix('doctor-chats')->group(function () {
                Route::get('/', [OnlineConsultationController::class, 'index']);
                Route::post('/create-doctor-chat', [OnlineConsultationController::class, 'store']);
                Route::get('/show/{onlineConsultation}', [OnlineConsultationController::class, 'show']);
                // Route::post('/create-bill-doctor-chat', [ChatDoctorBillController::class, 'store']);
                // Route::post('/callback', [ChatDoctorBillController::class, 'callback']);
                Route::post('/send-message/{onlineConsultation}', [ChatDoctorController::class, 'store']);
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
                Route::get('/previous', [ConsultationController::class, 'getPreviousConsultation']);
                Route::put('/complete/{appointment}', [ConsultationController::class, 'complete']);
                Route::put('/call-patient/{appointment}', [ConsultationController::class, 'callPatient']);
                Route::put('/{appointment}/status', [ConsultationController::class, 'updateStatus']);
            });
            Route::prefix('revenue')->group(function () {
                Route::get('/month', [BillController::class, 'getMyRevenue']);
                Route::get('/daily', [BillController::class, 'getMyDailyRevenue']);
            });
            Route::prefix('notifications')->group(function () {
                Route::get('/all', [ClinicNotificationController::class, 'getNotifications']); // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [ClinicNotificationController::class, 'markAsRead']); // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [ClinicNotificationController::class, 'markAllAsRead']); // Tandai semua notifikasi sebagai sudah dibaca
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
            Route::prefix('notifications')->group(function () {
                Route::get('/', [ClinicNotificationController::class, 'getNotifications']); // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [ClinicNotificationController::class, 'markAsRead']); // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [ClinicNotificationController::class, 'markAllAsRead']); // Tandai semua notifikasi sebagai sudah dibaca
            });
        });
    });
    Route::prefix('clinic')->group(function () {
        Route::get('/', [ClinicController::class, 'index']);
        Route::get('/nearby', [ClinicController::class, 'nearby']);
        Route::get('/show/{slug}', [ClinicController::class, 'show']);
        Route::get('/doctor-schedule/resource', [DoctorScheduleController::class, 'scheduleResource']);
        Route::middleware(['auth:sanctum', 'abilities:clinic'])->group(function () {
            Route::prefix('me')->group(function () {
                Route::get('/logout-clinic', [ClinicAuthController::class, 'logout']);
                Route::get('/user', [ClinicDataController::class, 'me']);
                Route::post('/update-profile-request', [ClinicUpdateRequestController::class, 'requestUpdate']);
                Route::get('/clinic-patient', [ClinicProfileController::class, 'clinicPatient']);
                Route::post('/{clinic}/images', [ClinicImageController::class, 'store']);
                Route::post('/store-profile-image', [ClinicImageController::class, 'storeProfile']);
            });
            Route::prefix('notifications')->group(function () {
                Route::get('/all', [ClinicNotificationController::class, 'getNotifications']); // Ambil notifikasi yang belum dibaca
                Route::put('/mark-read/{id}', [ClinicNotificationController::class, 'markAsRead']); // Tandai satu notifikasi sebagai sudah dibaca
                Route::put('/mark-all-read', [ClinicNotificationController::class, 'markAllAsRead']); // Tandai semua notifikasi sebagai sudah dibaca
            });
            Route::prefix('invoice')->group(function () {
                Route::get('/', [ClinicInvoiceController::class, 'index']);
                Route::post('/', [ClinicInvoiceController::class, 'store']);
                Route::get('/confirm/{clinicInvoice}', [ClinicInvoiceController::class, 'completed']);
                Route::get('/{clinicInvoice}', [ClinicInvoiceController::class, 'show']);
                Route::delete('/{clinicInvoice}', [ClinicInvoiceController::class,'destroy']);
                Route::prefix('items')->group(function () {
                    Route::delete('/{clinicInvoiceItem}', [ClinicInvoiceItemController::class, 'destroy']);
                });
            });

            Route::prefix('report-information')->group(function () {
                Route::get('/total-sales', [ClinicReportController::class, 'totalSales']);
                Route::get('/invoices', [ClinicReportController::class, 'invoices']);
                Route::get('/total-cash', [ClinicReportController::class, 'totalCash']);
                Route::get('/total-orders', [ClinicReportController::class, 'totalOrders']);
                Route::get('/total-vouchers', [ClinicReportController::class, 'totalVouchers']);
                Route::get('/total-locums', [ClinicReportController::class, 'totalLocums']);
                Route::get('/average-charge-per-patient', [ClinicReportController::class, 'averageChargePerPatient']);
                Route::get('/locum', [ClinicReportController::class, 'locum']);
                Route::get('/commonly-prescribe-medicine', [ClinicReportController::class, 'commonlyPrescribeMedicine']);
            });

            Route::prefix('expenses')->group(function () {
                Route::get('/', [ClinicExpenseController::class, 'index']);
                Route::post('/', [ClinicExpenseController::class, 'store']);
                Route::get('/{clinicExpense}', [ClinicExpenseController::class, 'show']);
                Route::put('/{clinicExpense}/confirm', [ClinicExpenseController::class, 'completed']);
                Route::delete('/{clinicExpense}', [ClinicExpenseController::class, 'destroy']);
            });
        });
        Route::middleware(['auth:sanctum', 'abilities:hasAccessResource'])->group(function () {
            Route::prefix('web-push')->group(function () {
                Route::post('/save-notification', [PushNotificationController::class, 'saveSubscription']);
            });
            Route::get('/clinic-information', [ClinicController::class, 'clinicInformation']);
            Route::get('inventory', [InventoryController::class, 'index']);
            Route::prefix('patient')->group(function () {
                Route::get('/search', [PatientController::class, 'search']);
                Route::post('/store', [PatientController::class, 'store']);
            });
            Route::prefix('consultation')->group(function () {
                Route::put('/complete/{appointment}', [ConsultationController::class, 'complete']);
                Route::put('/call-patient/{appointment}', [ConsultationController::class, 'callPatient']);
                Route::put('/call-patient-vital-sign/{appointment}', [ConsultationController::class, 'callPatientVitalSign']);
                Route::put('/call-patient-dispensary/{appointment}', [ConsultationController::class, 'callPatientDispensary']);
            });
            Route::prefix('medicines')->group(function () {
                Route::get('/', [MedicationController::class, 'index']);
                Route::get('/doctor-resource', [MedicationController::class, 'doctorResource']);
                Route::get('/drug-in-pregnancy', [MedicationController::class, 'drugInPregnancy']);
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
                Route::get('patients', [DoctorController::class, 'patients']);
                Route::put('patients/{billId}', [DoctorController::class, 'updatePatient']);
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
                Route::put('/update/{staff}', [ClinicDataController::class, 'updateStaff']);
                Route::delete('/delete/{staff}', [ClinicDataController::class, 'destroyStaff']);
                Route::apiResource('schedule', StaffClinicScheduleController::class);
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
                Route::get('/patient/queue', [PatientController::class, 'queue']);
            });

            Route::prefix('schedule')->group(function () {
                Route::post('/store', [ClinicScheduleController::class, 'store']);
                Route::put('/update/{clinicSchedule}', [ClinicScheduleController::class, 'update']);
            });

            Route::prefix('location')->group(function () {
                Route::post('/store', [ClinicLocationController::class, 'store']);
                Route::put('/update/{clinicLocation}', [ClinicLocationController::class, 'update']);
            });

            Route::prefix('settlements')->group(function () {
                Route::get('/', [ClinicDataController::class, 'getSettlements']);
                Route::put('/{clinicSettlement}/upload', [ClinicSettlementController::class, 'upload']);
            });
            Route::prefix('panel')->group(function () {
                Route::get('/', [PanelClinicController::class, 'index']); // List all panels
                Route::post('/store', [PanelClinicController::class, 'store']); // Store new panel
                Route::get('/show/{panelClinic}', [PanelClinicController::class, 'show']); // Show a specific panel
                Route::put('/update/{panelClinic}', [PanelClinicController::class, 'update']); // Update a panel
                Route::delete('/delete/{panelClinic}', [PanelClinicController::class, 'destroy']); // Update a panel
                Route::get('/resources', [PanelClinicController::class, 'resource']);
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
            Route::get('/clinic-transaction', [BillController::class, 'clinicTransaction']);
            Route::get('/clinic-total-revenue-month', [BillController::class, 'clinicTotalRevenue']);
            Route::get('/clinic-total-revenue-daily', [BillController::class, 'clinicDailyTotalRevenue']);
            Route::get('/clinic-total-revenue-doctor', [BillController::class, 'clinicTotalRevenueByDoctor']);
        });
        Route::prefix('appointments')->group(function () {
            Route::prefix('doctor')->group(function () {
                Route::get('/show/{slug}', [DoctorDataController::class, 'showConsultation']);
                Route::get('/consultation-entry', [DoctorDataController::class, 'consultationEntry']);
                Route::get('/pending-entry', [DoctorDataController::class, 'pendingEntry']);
                Route::get('/completed-entry', [DoctorDataController::class, 'completedEntry']);
                Route::delete('/cancel-appointment/{slug}', [AppointmentController::class, 'destroy']);
            });

            Route::post('/store', [AppointmentController::class, 'store']);

            Route::get('/dispensary', [ConsultationController::class, 'dispensary']);
            Route::get('/consultation-entry', [ConsultationController::class, 'consultationEntry']);
            Route::put('/take-medicine/{appointment}', [ConsultationController::class, 'takeMedicine']);
            Route::put('/{appointment}/change-doctor', [ConsultationController::class, 'changeDoctor']);
        });
        Route::prefix('diagnosis')->group(function () {
            Route::get('/', [DiagnosisController::class, 'index']);
        });
        Route::prefix('pregnancy-category')->group(function () {
            Route::get('/', [PregnancyCategoryController::class, 'index']);
            Route::get('/show/{pregnancyCategory}', [PregnancyCategoryController::class, 'show']);
        });
        Route::prefix('rooms')->group(function () {
            Route::get('/', [RoomController::class, 'index']);
            Route::get('/resource', [RoomController::class, 'roomResource']);
        });
        Route::prefix('attendance')->group(function () {
            Route::get('/show/{attendance}', [AttendanceController::class, 'show']);
            Route::get('/daily', [AttendanceController::class, 'checkTodayAttendance']);
            Route::get('/total-working-month', [AttendanceController::class, 'getTotalWorkingMonth']);
            Route::get('/', [AttendanceController::class, 'index']);
            Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
            Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
        });
        Route::prefix('chat')->group(function () {
            Route::get('/get-chat', [MessageClinicoController::class, 'index']);
            Route::get('/get-message/{user}', [MessageClinicoController::class, 'getMessages']);
            Route::post('/send-message', [MessageClinicoController::class, 'sendMessage']);
            Route::get('/history-chat/{user}', [MessageClinicoController::class, 'getChatHistory']);
            Route::get('/total-unread-messages', [MessageClinicoController::class, 'getTotalUnreadMessages']);
        });


        //================== Permission (Overtime, Claim, Leave) ==================//
        Route::prefix('permission')->group(function () {
            $permissionTypes = [
                'overtime' => OvertimePermissionController::class,
                'claim' => ClaimPermissionController::class,
                'leave' => LeavePermissionController::class,
            ];

            foreach ($permissionTypes as $type => $controller) {
                Route::get("/$type", [$controller, 'index']);
                Route::post("/$type", [$controller, 'store']);
                Route::get("/$type/{id}", [$controller, 'show']);
                Route::put("/$type/{id}/approve", [$controller, 'approve']);
                Route::put("/$type/{id}/decline", [$controller, 'decline']);
                Route::delete("/$type/{id}", [$controller, 'destroy']);
            }

            Route::prefix('leave-balances')->group(function () {
                Route::get('/', [LeaveBalanceController::class, 'index']);
            });

            Route::get('leave-types/details', [LeaveTypeDetailController::class, "index"]);
            Route::put('leave-types/details/{id}', [LeaveTypeDetailController::class, "update"]);
            Route::apiResource('leave-types', LeaveTypeController::class);
            Route::apiResource('claim-items', ClaimItemController::class);
        });

        //================== Statistic ==================//
        Route::prefix('statistic')->group(function() {
            Route::get('/patient', [StatisticController::class, 'consultationCompleted']);
        });

        //================== History ==================//
        Route::prefix('history')->group(function() {
            Route::get('/medical', [MedicalRecordController::class, 'history']);
        });

        //================== Payslip ==================//
        Route::prefix('payslip')->group(function () {
            Route::get('/', [MonthlyPayslipController::class, 'index']);
            Route::get('/{id}', [MonthlyPayslipController::class, 'show']);
            Route::post('/', [MonthlyPayslipController::class, 'store']);
            Route::delete('/{id}', [MonthlyPayslipController::class, 'destroy']);
            Route::put('/{id}', [MonthlyPayslipController::class, 'update']);
        });
    });

    Route::middleware('auth:sanctum')->group(function() {
        //================== Report Bugs ==================/
        Route::prefix('report-bugs')->group(function() {
            Route::prefix('/types')->group(function() {
                Route::get('/', [ReportBugTypeController::class, 'index']);
            });
            Route::get('/', [ReportBugController::class, 'index']);
            Route::post('/', [ReportBugController::class, 'store']);
            Route::get('/{id}', [ReportBugController::class, 'show']);
            Route::put('/{id}', [ReportBugController::class, 'update']);
            Route::delete('/{id}', [ReportBugController::class, 'destroy']);
            Route::post('/{id}/send-email', [ReportBugController::class, 'sendEmail']);
            Route::put('/{id}/resolve', [ReportBugController::class, 'resolve']);
        });

        Route::get('employees', [EmployeeController::class, 'index']);
    });
});
