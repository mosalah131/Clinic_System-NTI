<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PHASE 5 - Dashboard statistics.
 *
 * One method per role. Each one counts real rows in the database and hands the
 * numbers to the matching Blade view.
 */
class DashboardController extends Controller
{
    /* ==================================================================
     | ADMIN DASHBOARD
     | =================================================================*/
    public function admin(): View
    {
        $stats = [
            'patients'     => Patient::count(),
            'doctors'      => Doctor::count(),
            'departments'  => Department::count(),
            'appointments' => Appointment::count(),
            'today'        => Appointment::today()->count(),
            'pending'      => Appointment::where('status', Appointment::STATUS_PENDING)->count(),
            'accepted'     => Appointment::where('status', Appointment::STATUS_ACCEPTED)->count(),
            'rejected'     => Appointment::where('status', Appointment::STATUS_REJECTED)->count(),
            'cancelled'    => Appointment::where('status', Appointment::STATUS_CANCELLED)->count(),
            'completed'    => Appointment::where('status', Appointment::STATUS_COMPLETED)->count(),
        ];

        // "with(...)" loads the related rows in the same query instead of one
        // query per row - this is what "eager loading" means.
        $todayAppointments = Appointment::with(['patient.user', 'doctor.user'])
            ->today()
            ->orderBy('appointment_time')
            ->get();

        return view('admin.dashboard', compact('stats', 'todayAppointments'));
    }

    /* ==================================================================
     | DOCTOR DASHBOARD
     | =================================================================*/
    public function doctor(): View
    {
        $doctor = Auth::user()->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        $stats = [
            'today'     => $doctor->appointments()->today()->count(),
            'pending'   => $doctor->appointments()->where('status', Appointment::STATUS_PENDING)->count(),
            'completed' => $doctor->appointments()->where('status', Appointment::STATUS_COMPLETED)->count(),
            'patients'  => $doctor->appointments()->distinct('patient_id')->count('patient_id'),
        ];

        $todayAppointments = $doctor->appointments()
            ->with('patient.user')
            ->today()
            ->orderBy('appointment_time')
            ->get();

        $recentPrescriptions = Prescription::with(['patient.user', 'appointment'])
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->take(5)
            ->get();

        return view('doctor.dashboard', compact('doctor', 'stats', 'todayAppointments', 'recentPrescriptions'));
    }

    /* ==================================================================
     | PATIENT DASHBOARD
     | =================================================================*/
    public function patient(): View
    {
        $patient = Auth::user()->patient;

        abort_if(! $patient, 403, 'Your patient profile has not been created yet.');

        $stats = [
            'upcoming'      => $patient->appointments()->upcoming()
                                       ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_ACCEPTED])
                                       ->count(),
            'pending'       => $patient->appointments()->where('status', Appointment::STATUS_PENDING)->count(),
            'prescriptions' => $patient->prescriptions()->count(),
            'analyses'      => $patient->analyses()->count(),
        ];

        // The very next confirmed or requested visit.
        $nextAppointment = $patient->appointments()
            ->with('doctor.user', 'doctor.department')
            ->upcoming()
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_ACCEPTED])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        $recentPrescriptions = $patient->prescriptions()
            ->with(['doctor.user', 'appointment'])
            ->latest()
            ->take(5)
            ->get();

        return view('patient.dashboard', compact('patient', 'stats', 'nextAppointment', 'recentPrescriptions'));
    }

    /* ==================================================================
     | RECEPTION DASHBOARD
     | =================================================================*/
    public function reception(): View
    {
        $stats = [
            'patients'      => Patient::count(),
            'doctors'       => Doctor::count(),
            'todayPatients' => Appointment::today()->distinct('patient_id')->count('patient_id'),
            'today'         => Appointment::today()->count(),
            'pending'       => Appointment::where('status', Appointment::STATUS_PENDING)->count(),
            'cancelled'     => Appointment::where('status', Appointment::STATUS_CANCELLED)->count(),
        ];

        $todayAppointments = Appointment::with(['patient.user', 'doctor.user', 'doctor.department'])
            ->today()
            ->orderBy('appointment_time')
            ->get();

        return view('reception.dashboard', compact('stats', 'todayAppointments'));
    }
}
