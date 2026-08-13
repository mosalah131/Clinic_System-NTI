<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PHASE 3 - Doctor logic.
 *
 * The doctor sees ONLY their own appointments and is the only role that may
 * accept or reject them.
 */
class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->doctor();

        $search = $request->query('search');
        $status = $request->query('status');
        $date   = $request->query('date');

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'prescription'])
            ->when($search, fn ($q) => $q->whereHas('patient.user', fn ($u) => $u->where('name', 'like', "%{$search}%")))
            ->status($status)
            ->when($date, fn ($q) => $q->whereDate('appointment_date', $date))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('doctor.appointments', compact('doctor', 'appointments', 'search', 'status', 'date'));
    }

    /**
     * The full details of one appointment: patient info, symptoms, uploaded
     * analyses and the prescription (if it exists yet).
     */
    public function show(Appointment $appointment): View
    {
        $this->authorizeOwnership($appointment);

        $appointment->load([
            'patient.user',
            'patient.analyses',
            'analyses',
            'prescription.medicines',
        ]);

        return view('doctor.appointment-show', [
            'doctor'      => $this->doctor(),
            'appointment' => $appointment,
        ]);
    }

    /** ACCEPT - only the doctor, only while the request is pending. */
    public function accept(Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwnership($appointment);

        if (! $appointment->canBeReviewed()) {
            return back()->with('error', 'Only a pending appointment can be accepted.');
        }

        $appointment->update(['status' => Appointment::STATUS_ACCEPTED]);

        ActivityLog::record('appointment.accepted', "Accepted appointment #{$appointment->id}");

        return back()->with('success', 'Appointment accepted.');
    }

    /** REJECT - only the doctor, only while the request is pending. */
    public function reject(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwnership($appointment);

        if (! $appointment->canBeReviewed()) {
            return back()->with('error', 'Only a pending appointment can be rejected.');
        }

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->update([
            'status'        => Appointment::STATUS_REJECTED,
            'cancel_reason' => $data['cancel_reason'] ?? 'Rejected by the doctor',
        ]);

        ActivityLog::record('appointment.rejected', "Rejected appointment #{$appointment->id}");

        return back()->with('success', 'Appointment rejected.');
    }

    /* ------------------------------------------------------------------
     | Small helpers
     | -----------------------------------------------------------------*/

    private function doctor()
    {
        $doctor = Auth::user()->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        return $doctor;
    }

    /** A doctor must never be able to open another doctor's appointment. */
    private function authorizeOwnership(Appointment $appointment): void
    {
        abort_if($appointment->doctor_id !== $this->doctor()->id, 403,
            'This appointment belongs to another doctor.');
    }
}
