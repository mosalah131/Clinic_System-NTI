<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PHASE 3 - Patient logic.
 *
 * The patient may book an appointment and cancel it, and nothing else:
 * accepting / rejecting belongs to the doctor.
 */
class AppointmentController extends Controller
{
    /** "My Appointments" */
    public function index(Request $request): View
    {
        $patient = $this->patient();

        $search = $request->query('search');
        $status = $request->query('status');
        $date   = $request->query('date');

        $appointments = $patient->appointments()
            ->with(['doctor.user', 'doctor.department', 'prescription'])
            ->when($search, fn ($q) => $q->whereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$search}%")))
            ->status($status)
            ->when($date, fn ($q) => $q->whereDate('appointment_date', $date))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('patient.appointments', compact('patient', 'appointments', 'search', 'status', 'date'));
    }

    /** The "Book Appointment" form. */
    public function create(): View
    {
        return view('patient.book-appointment', [
            'patient'     => $this->patient(),
            'departments' => Department::orderBy('name')->get(),
            // Sent to the page as JSON so the doctor dropdown can be filtered
            // by department without reloading.
            'doctors'     => Doctor::with(['user', 'department'])
                ->whereHas('user', fn ($u) => $u->where('status', 'active'))
                ->get()
                ->map(fn ($d) => [
                    'id'            => $d->id,
                    'name'          => $d->display_name,
                    'department_id' => $d->department_id,
                    'fee'           => $d->consultation_fee,
                    'specialization'=> $d->specialization,
                ]),
            'timeSlots'   => config('clinic.time_slots'),
        ]);
    }

    /** BOOK - the new appointment always starts as "pending". */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = $this->patient();

        $data = $request->validated();

        // Is that doctor already booked at this exact moment?
        $taken = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereTime('appointment_time', $data['appointment_time'])
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_REJECTED])
            ->exists();

        if ($taken) {
            return back()->withInput()
                ->with('error', 'That time slot is already taken. Please choose another one.');
        }

        // Does the patient already have a booking with the same doctor that day?
        $duplicate = $patient->appointments()
            ->where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_ACCEPTED])
            ->exists();

        if ($duplicate) {
            return back()->withInput()
                ->with('error', 'You already have an appointment with this doctor on that day.');
        }

        $appointment = Appointment::create($data + [
            'patient_id' => $patient->id,
            'status'     => Appointment::STATUS_PENDING,
        ]);

        ActivityLog::record('appointment.booked', "Patient booked appointment #{$appointment->id}");

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Your appointment has been requested. The doctor will confirm it shortly.');
    }

    /** CANCEL - allowed while the appointment is still pending or accepted. */
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $patient = $this->patient();

        abort_if($appointment->patient_id !== $patient->id, 403, 'This is not your appointment.');

        if (! $appointment->canBeCancelled()) {
            return back()->with('error', 'A '.$appointment->status.' appointment cannot be cancelled.');
        }

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->update([
            'status'        => Appointment::STATUS_CANCELLED,
            'cancel_reason' => $data['cancel_reason'] ?? 'Cancelled by the patient',
        ]);

        ActivityLog::record('appointment.cancelled', "Patient cancelled appointment #{$appointment->id}");

        return back()->with('success', 'Your appointment has been cancelled.');
    }

    private function patient()
    {
        $patient = Auth::user()->patient;

        abort_if(! $patient, 403, 'Your patient profile has not been created yet.');

        return $patient;
    }
}
