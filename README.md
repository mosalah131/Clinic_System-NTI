# Clinic Management System

A complete clinic management system built with **Laravel 12 + MySQL**, implementing
the five phases described in the project requirements document.

> **New here?** Read `../SETUP-GUIDE.md` first — it explains how to install
> everything and run the project step by step.

**Quick start (if the software is already installed):**

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Then open <http://localhost:8000>. Every account's password is `password123`.

| Role | Email |
|---|---|
| Admin | `admin@clinic.com` |
| Doctor | `mohamed@clinic.com` |
| Reception | `reception@clinic.com` |
| Patient | `ahmed@clinic.com` |

---

## 1. Database schema

13 tables, exactly as specified in the requirements document.

| Table | Purpose | Soft delete |
|---|---|---|
| `users` | Every account. The `role` column (`admin`/`doctor`/`reception`/`patient`) decides what they may do. | ✅ |
| `departments` | Clinic departments (Cardiology, Dentistry …). | ✅ |
| `doctors` | Professional profile of a user whose role is `doctor`. | ✅ |
| `doctor_schedules` | Weekly working hours per doctor. | — |
| `patients` | Medical profile of a user whose role is `patient`. | ✅ |
| `appointments` | **The central entity.** Merged with the diagnosis, as specified. | ✅ |
| `medicines` | The catalogue a doctor selects from. | ✅ |
| `prescriptions` | One per completed appointment. | — |
| `medicine_prescription` | Many-to-many pivot carrying `dosage`, `frequency`, `duration`. | — |
| `medical_files` | Files uploaded by staff (doctor/admin) about a patient. | — |
| `analyses` | Medical analyses uploaded by the patient. | — |
| `activity_logs` | Audit trail (bonus feature). | — |
| `password_reset_tokens` | Laravel's standard table. | — |

### Relationships

```
User      1 ─── 1  Doctor            (hasOne / belongsTo)
User      1 ─── 1  Patient           (hasOne / belongsTo)
Department 1 ─── n Doctors           (hasMany)
Doctor    1 ─── n  DoctorSchedules   (hasMany)
Doctor    1 ─── n  Appointments      (hasMany)
Patient   1 ─── n  Appointments      (hasMany)
Patient   1 ─── n  MedicalFiles      (hasMany)
Patient   1 ─── n  Analyses          (hasMany)
Appointment 1 ─ 1  Prescription      (hasOne)
Appointment 1 ─ n  Analyses          (hasMany)
Prescription n ─ n Medicines         (belongsToMany, via medicine_prescription)
```

### Two notes on the requirements document

The requirements document names a few things twice, in two different ways.
Both were resolved as follows, and both features are fully implemented:

1. **Pivot table name.** The schema section calls it `medicine_prescription`;
   Phase 4 calls it `prescription_medicine`. The project uses
   **`medicine_prescription`**, which is Laravel's naming convention (the two
   table names in singular, alphabetical order).
2. **File uploads.** The schema section defines `medical_files`; Phase 4 defines
   `analyses`. These are two different features in the frontend
   (*Doctor → Patient Files* and *Patient → My Analysis*), so **both tables exist**:
   `medical_files` holds what the staff uploads, `analyses` holds what the patient uploads.

Three columns were **added** to `medicines` (`category`, `price`, `quantity`,
`status`) because the supplied Medicines screen displays those columns. Everything
from the original schema is unchanged.

---

## 2. Phase 1 — Authentication, roles, middleware, route protection

- `app/Http/Controllers/AuthController.php` — register / login / logout.
- One login page for all four roles; after the password is verified the `role`
  column decides which dashboard you land on (`User::homeRoute()`).
- Four middleware classes in `app/Http/Middleware/`:
  `AdminMiddleware`, `DoctorMiddleware`, `ReceptionMiddleware`, `PatientMiddleware`.
- Registered as the aliases `admin`, `doctor`, `reception`, `patient` in
  `bootstrap/app.php`, and attached to every route group in `routes/web.php`.
- Not logged in → redirected to the login page. Wrong role → **403 Forbidden**.
- Deactivated accounts (`users.status = 'inactive'`) cannot log in.

---

## 3. Phase 2 — Controllers, models, relationships

**Models** (`app/Models/`): `User`, `Department`, `Doctor`, `DoctorSchedule`,
`Patient`, `Appointment`, `Medicine`, `Prescription`, `MedicalFile`, `Analysis`,
`ActivityLog`.

**Controllers** (`app/Http/Controllers/`), grouped by role:

| Controller | Responsibility |
|---|---|
| `AuthController` | Register, login, logout |
| `DashboardController` | The statistics for all four dashboards |
| `ProfileController` | "My Profile" / "Settings" + change password, all roles |
| `Admin\DepartmentController` | Department CRUD |
| `Admin\DoctorController` | Doctor CRUD (creates the user + the doctor profile in one transaction) |
| `Admin\PatientController` | Patient CRUD |
| `Admin\MedicineController` | Medicine CRUD |
| `Admin\AppointmentController` | Appointment CRUD + status changes |
| `Doctor\AppointmentController` | The doctor's own appointments, accept / reject |
| `Doctor\PrescriptionController` | Diagnosis + prescription |
| `Doctor\PatientController` | "My Patients" and their history |
| `Doctor\MedicalFileController` | Upload / delete patient files |
| `Patient\AppointmentController` | Book, view, cancel |
| `Patient\PrescriptionController` | Read prescriptions |
| `Patient\AnalysisController` | Upload / delete analyses |
| `Reception\PatientController` | Register and edit patients |
| `Reception\AppointmentController` | Create, reschedule, cancel |

---

## 4. Phase 3 — Business logic

### Appointment life cycle

```
                    ┌──────────► rejected  (doctor only)
                    │
patient books ──► pending ──► accepted ──► completed
                    │              │
                    └──────────────┴─────► cancelled
```

### Enforced rules

| Rule | Where it lives |
|---|---|
| Only a **pending** appointment can be accepted or rejected | `Appointment::canBeReviewed()` |
| Only a **pending or accepted** appointment can be cancelled | `Appointment::canBeCancelled()` |
| A **cancelled or completed** appointment cannot be edited | `Appointment::canBeEdited()` |
| A prescription needs an **accepted** appointment | `Appointment::canHavePrescription()` |
| A **completed** appointment can never be deleted | `Appointment::canBeDeleted()` |
| The diagnosis must exist before the prescription | `Doctor\PrescriptionController::storePrescription()` |
| A doctor cannot be double-booked at the same date + time | `slotTaken()` in the appointment controllers |
| A doctor can only open their **own** appointments and patients | `authorizeOwnership()` |
| A patient can only cancel or attach files to **their own** appointments | `Patient\*Controller` |

### Permission matrix (as specified)

| Operation | Patient | Doctor | Reception | Admin |
|---|:--:|:--:|:--:|:--:|
| Book appointment | ✅ | ❌ | ✅ | ✅ |
| Edit appointment | ❌ | ❌ | ✅ | ✅ |
| Cancel appointment | ✅ | ❌ | ✅ | ✅ |
| Accept appointment | ❌ | ✅ | ❌ | ✅ |
| Reject appointment | ❌ | ✅ | ❌ | ✅ |
| Write diagnosis | ❌ | ✅ | ❌ | — |
| Write prescription | ❌ | ✅ | ❌ | — |
| Upload analysis | ✅ | ❌ | ❌ | — |
| Upload medical file | ❌ | ✅ | ❌ | — |
| Manage doctors | ❌ | ❌ | ❌ | ✅ |
| Manage departments | ❌ | ❌ | ❌ | ✅ |
| Manage medicines | ❌ | ❌ | ❌ | ✅ |

**One deliberate difference.** The requirements table marks the admin ✅ for
*write diagnosis*, *write prescription* and *upload analysis*, but the supplied
frontend has no admin screen for any of them — those are clinical acts performed
by the treating doctor and the patient. The admin therefore has **full control over
appointments** (create, edit, accept, reject, cancel, delete, restore) but writes
clinical records through a doctor account. If you need admin screens for these,
they are a small addition on top of the existing controllers.

---

## 5. Phase 4 — Medical records

- **Prescriptions** — `Doctor\PrescriptionController`. Saving a prescription
  automatically sets the appointment to `completed`. Editing an existing
  prescription uses `updateOrCreate` + `sync()`, so the medical record is
  corrected rather than duplicated. Prescriptions are never deleted.
- **Medicines** — only the admin may add / edit / delete. The doctor *selects*
  from the catalogue; the dosage, frequency and duration are stored on the pivot
  row, so the same medicine can appear in thousands of prescriptions with
  different instructions.
- **Analyses** — the patient uploads PDF / JPG / PNG / DOCX. Laravel validates
  the extension and the size (10 MB by default, set in `config/clinic.php`).
  The file goes to `storage/app/public/analyses/` and only the path is stored in
  the database. The doctor sees it when opening the appointment.

---

## 6. Phase 5 — Finishing and quality

- **Dashboard statistics** — every number on all four dashboards is a real
  `COUNT` against the database (`DashboardController`).
- **Soft delete** — enabled on `users`, `departments`, `doctors`, `patients`,
  `appointments` and `medicines`. Every admin list page has an **Active / Deleted**
  switch with a **Restore** button.
- **Validation** — every create and update validates its input with clear,
  human-readable messages. The five operations the requirements document calls out
  by name use dedicated **Form Request** classes in `app/Http/Requests/`
  (`RegisterRequest`, `StoreAppointmentRequest`, `StorePrescriptionRequest`,
  `StoreAnalysisRequest`, `StoreMedicineRequest`); the remaining CRUD operations
  validate inline with `$request->validate()`, which runs the identical validator.
- **Error handling** — custom `403`, `404`, `419`, `500` and `503` pages in
  `resources/views/errors/`, styled to match the rest of the system.
- **Activity log** — every important action is recorded in `activity_logs`
  through `ActivityLog::record()`.
- **Responsive** — Bootstrap 5 grid; the sidebar collapses on small screens.

---

## 7. Useful commands

| Command | What it does |
|---|---|
| `php artisan serve` | Start the website on <http://localhost:8000> |
| `php artisan migrate` | Create the database tables |
| `php artisan migrate:fresh --seed` | Delete everything and rebuild with fresh demo data |
| `php artisan db:seed` | Add the demo data to an existing database |
| `php artisan route:list` | Show every address the site answers |
| `php artisan storage:link` | Connect the uploads folder to the browser |
| `php artisan optimize:clear` | Clear all caches when something behaves strangely |
| `php artisan tinker` | An interactive console for poking at the database |

---

## 8. Requirements

- PHP **8.2** or newer (XAMPP 8.2+)
- MySQL 5.7+ / MariaDB 10.3+
- Composer 2
