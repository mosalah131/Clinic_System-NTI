<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reception manages appointments for every patient:
 * create, edit (doctor / date / time) and cancel.
 *
 * Reception may NOT accept or reject - that is the doctor's decision.
 */
class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $date   = $request->query('date');

        $appointments = Appointment::with(['patient.user', 'doctor.user', 'doctor.department'])
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('patient.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                ->orWhereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->status($status)
            ->when($date, fn ($q) => $q->whereDate('appointment_date', $date))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('reception.appointments', [
            'appointments' => $appointments,
            'patients'     => Patient::with('user')->get(),
            'doctors'      => Doctor::with(['user', 'department'])->get(),
            'departments'  => Department::orderBy('name')->get(),
            'timeSlots'    => config('clinic.time_slots'),
            'search'       => $search,
            'status'       => $status,
            'date'         => $date,
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($this->slotTaken($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return back()->withInput()->with('error', 'This doctor already has an appointment at that time.');
        }

        $appointment = Appointment::create($data + ['status' => Appointment::STATUS_PENDING]);

        ActivityLog::record('appointment.created', "Reception created appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been created and is waiting for the doctor.');
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        if (! $appointment->canBeEdited()) {
            return back()->with('error', 'A '.$appointment->status.' appointment cannot be modified.');
        }

        $data = $request->validate([
            'doctor_id'        => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->slotTaken($data['doctor_id'], $data['appointment_date'], $data['appointment_time'], $appointment->id)) {
            return back()->with('error', 'This doctor already has an appointment at that time.');
        }

        $appointment->update($data);

        ActivityLog::record('appointment.updated', "Reception updated appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been updated.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        if (! $appointment->canBeCancelled()) {
            return back()->with('error', 'A '.$appointment->status.' appointment cannot be cancelled.');
        }

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment->update([
            'status'        => Appointment::STATUS_CANCELLED,
            'cancel_reason' => $data['cancel_reason'] ?? 'Cancelled at the reception desk',
        ]);

        ActivityLog::record('appointment.cancelled', "Reception cancelled appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been cancelled.');
    }

    private function slotTaken(int $doctorId, string $date, string $time, ?int $ignoreId = null): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereTime('appointment_time', $time)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_REJECTED])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
