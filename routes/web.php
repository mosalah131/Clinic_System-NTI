<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Doctor;
use App\Http\Controllers\Patient;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every address the browser can visit is listed here.
|
| The middleware in the second argument of Route::middleware([...]) is the
| security guard: "auth" means "you must be logged in", and "admin" /
| "doctor" / "reception" / "patient" mean "your role column must be this".
|
*/

/* ======================================================================
 | Home - sends you to the right place
 | =====================================================================*/
Route::get('/', function () {
    return Auth::check()
        ? redirect(Auth::user()->homeRoute())
        : redirect()->route('login');
})->name('home');

/* ======================================================================
 | Uploaded files (safety net)
 |
 | Normally "php artisan storage:link" creates a shortcut at public/storage
 | and Apache serves the uploaded files directly. On some Windows machines
 | that shortcut cannot be created, so this route serves the same files
 | through Laravel instead. It only ever reads from storage/app/public and
 | rejects any attempt to escape that folder.
 | =====================================================================*/
Route::get('/storage/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);

    $full = storage_path('app/public/'.$path);

    abort_unless(is_file($full), 404);

    return response()->file($full);
})->where('path', '.*')->name('storage.file');

/* ======================================================================
 | Authentication (open to guests)
 | =====================================================================*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ======================================================================
 | Shared by every logged-in user: editing your own profile / password
 | =====================================================================*/
Route::middleware('auth')->group(function () {
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

/* ======================================================================
 | ADMIN            /admin/...        protected by AdminMiddleware
 | =====================================================================*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // --- Departments -------------------------------------------------
    Route::get('/departments', [Admin\DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [Admin\DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [Admin\DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [Admin\DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::post('/departments/{id}/restore', [Admin\DepartmentController::class, 'restore'])->name('departments.restore');

    // --- Doctors -----------------------------------------------------
    Route::get('/doctors', [Admin\DoctorController::class, 'index'])->name('doctors.index');
    Route::post('/doctors', [Admin\DoctorController::class, 'store'])->name('doctors.store');
    Route::put('/doctors/{doctor}', [Admin\DoctorController::class, 'update'])->name('doctors.update');
    Route::delete('/doctors/{doctor}', [Admin\DoctorController::class, 'destroy'])->name('doctors.destroy');
    Route::post('/doctors/{id}/restore', [Admin\DoctorController::class, 'restore'])->name('doctors.restore');

    // --- Patients ----------------------------------------------------
    Route::get('/patients', [Admin\PatientController::class, 'index'])->name('patients.index');
    Route::post('/patients', [Admin\PatientController::class, 'store'])->name('patients.store');
    Route::put('/patients/{patient}', [Admin\PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}', [Admin\PatientController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{id}/restore', [Admin\PatientController::class, 'restore'])->name('patients.restore');

    // --- Medicines ---------------------------------------------------
    Route::get('/medicines', [Admin\MedicineController::class, 'index'])->name('medicines.index');
    Route::post('/medicines', [Admin\MedicineController::class, 'store'])->name('medicines.store');
    Route::put('/medicines/{medicine}', [Admin\MedicineController::class, 'update'])->name('medicines.update');
    Route::delete('/medicines/{medicine}', [Admin\MedicineController::class, 'destroy'])->name('medicines.destroy');
    Route::post('/medicines/{id}/restore', [Admin\MedicineController::class, 'restore'])->name('medicines.restore');

    // --- Appointments -------------------------------------------------
    Route::get('/appointments', [Admin\AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [Admin\AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [Admin\AppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/appointments/{appointment}/status', [Admin\AppointmentController::class, 'changeStatus'])->name('appointments.status');
    Route::delete('/appointments/{appointment}', [Admin\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/appointments/{id}/restore', [Admin\AppointmentController::class, 'restore'])->name('appointments.restore');

    // --- Settings (own profile + password) ----------------------------
    Route::get('/settings', [ProfileController::class, 'adminSettings'])->name('settings');
});

/* ======================================================================
 | DOCTOR           /doctor/...       protected by DoctorMiddleware
 | =====================================================================*/
Route::middleware(['auth', 'doctor'])->prefix('doctor')->name('doctor.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'doctor'])->name('dashboard');

    // --- My appointments ---------------------------------------------
    Route::get('/appointments', [Doctor\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [Doctor\AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{appointment}/accept', [Doctor\AppointmentController::class, 'accept'])->name('appointments.accept');
    Route::post('/appointments/{appointment}/reject', [Doctor\AppointmentController::class, 'reject'])->name('appointments.reject');

    // --- Diagnosis (Phase 4) ------------------------------------------
    Route::get('/diagnosis/{appointment?}', [Doctor\PrescriptionController::class, 'diagnosis'])->name('diagnosis');
    Route::post('/diagnosis/{appointment}', [Doctor\PrescriptionController::class, 'storeDiagnosis'])->name('diagnosis.store');

    // --- Prescription (Phase 4) ---------------------------------------
    Route::get('/prescription/{appointment?}', [Doctor\PrescriptionController::class, 'prescription'])->name('prescription');
    Route::post('/prescription/{appointment}', [Doctor\PrescriptionController::class, 'storePrescription'])->name('prescription.store');

    // --- My patients ---------------------------------------------------
    Route::get('/patients', [Doctor\PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [Doctor\PatientController::class, 'show'])->name('patients.show');

    // --- Patient files --------------------------------------------------
    Route::get('/files', [Doctor\MedicalFileController::class, 'index'])->name('files.index');
    Route::post('/files', [Doctor\MedicalFileController::class, 'store'])->name('files.store');
    Route::delete('/files/{medicalFile}', [Doctor\MedicalFileController::class, 'destroy'])->name('files.destroy');

    Route::get('/profile', [ProfileController::class, 'doctorProfile'])->name('profile');
});

/* ======================================================================
 | PATIENT          /patient/...      protected by PatientMiddleware
 | =====================================================================*/
Route::middleware(['auth', 'patient'])->prefix('patient')->name('patient.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'patient'])->name('dashboard');

    // --- Appointments ----------------------------------------------------
    Route::get('/appointments', [Patient\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/book-appointment', [Patient\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [Patient\AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [Patient\AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // --- Prescriptions ---------------------------------------------------
    Route::get('/prescriptions', [Patient\PrescriptionController::class, 'index'])->name('prescriptions.index');

    // --- Medical analyses -------------------------------------------------
    Route::get('/analysis', [Patient\AnalysisController::class, 'index'])->name('analysis.index');
    Route::post('/analysis', [Patient\AnalysisController::class, 'store'])->name('analysis.store');
    Route::delete('/analysis/{analysis}', [Patient\AnalysisController::class, 'destroy'])->name('analysis.destroy');

    Route::get('/profile', [ProfileController::class, 'patientProfile'])->name('profile');
});

/* ======================================================================
 | RECEPTION        /reception/...    protected by ReceptionMiddleware
 | =====================================================================*/
Route::middleware(['auth', 'reception'])->prefix('reception')->name('reception.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'reception'])->name('dashboard');

    // --- Patients ---------------------------------------------------------
    Route::get('/patients', [Reception\PatientController::class, 'index'])->name('patients.index');
    Route::post('/patients', [Reception\PatientController::class, 'store'])->name('patients.store');
    Route::put('/patients/{patient}', [Reception\PatientController::class, 'update'])->name('patients.update');

    // --- Appointments -----------------------------------------------------
    Route::get('/appointments', [Reception\AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [Reception\AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [Reception\AppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/appointments/{appointment}/cancel', [Reception\AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('/profile', [ProfileController::class, 'receptionProfile'])->name('profile');
});
