<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * "My Patients" - the patients this doctor has actually seen.
 *
 * A doctor may look at their own patients but may NOT delete or create them.
 */
class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->doctor();
        $search = $request->query('search');

        // Only patients who have at least one appointment with this doctor.
        $patients = Patient::with('user')
            ->whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->withMax(
                ['appointments as last_appointment' => fn ($q) => $q->where('doctor_id', $doctor->id)],
                'appointment_date'
            )
            ->orderBy('id')
            ->get();

        return view('doctor.patients', compact('doctor', 'patients', 'search'));
    }

    /** One patient's full history with this doctor. */
    public function show(Patient $patient): View
    {
        $doctor = $this->doctor();

        // A doctor can only open a patient they have treated.
        abort_if(
            ! $patient->appointments()->where('doctor_id', $doctor->id)->exists(),
            403,
            'This patient has never had an appointment with you.'
        );

        $patient->load(['user', 'analyses']);

        $appointments = $patient->appointments()
            ->with('prescription.medicines')
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('appointment_date')
            ->get();

        return view('doctor.patient-show', compact('doctor', 'patient', 'appointments'));
    }

    private function doctor()
    {
        $doctor = Auth::user()->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        return $doctor;
    }
}
