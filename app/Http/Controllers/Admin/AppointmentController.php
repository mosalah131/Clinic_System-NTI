<?php

namespace App\Http\Controllers\Admin;

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
 * PHASE 3 - Appointments logic, admin side.
 *
 * The admin may create, edit, cancel, accept, reject and delete appointments.
 */
class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->query('search');
        $status   = $request->query('status');
        $doctorId = $request->query('doctor_id');
        $date     = $request->query('date');
        $trashed  = $request->boolean('trashed');

        $appointments = Appointment::with(['patient.user', 'doctor.user', 'doctor.department'])
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            // The extra where(...) wrapper keeps the OR inside its own brackets,
            // so it cannot swallow the other filters.
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('patient.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                ->orWhereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->status($status)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->when($date, fn ($q) => $q->whereDate('appointment_date', $date))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('admin.appointments', [
            'appointments' => $appointments,
            'doctors'      => Doctor::with(['user', 'department'])->get(),
            'patients'     => Patient::with('user')->get(),
            'departments'  => Department::orderBy('name')->get(),
            'search'       => $search,
            'status'       => $status,
            'doctorId'     => $doctorId,
            'date'         => $date,
            'trashed'      => $trashed,
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Business rule: the same doctor cannot have two appointments at the
        // exact same date and time.
        if ($this->slotTaken($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return back()->withInput()->with('error', 'This doctor already has an appointment at that time.');
        }

        $appointment = Appointment::create($data + ['status' => Appointment::STATUS_PENDING]);

        ActivityLog::record('appointment.created', "Admin created appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been created.');
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

        ActivityLog::record('appointment.updated', "Admin updated appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been updated.');
    }

    /**
     * Accept / reject / cancel / complete from the admin screen.
     */
    public function changeStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status'        => ['required', 'in:pending,accepted,rejected,cancelled,completed'],
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        // A finished visit is a medical record - it must not go backwards.
        if ($appointment->status === Appointment::STATUS_COMPLETED) {
            return back()->with('error', 'A completed appointment cannot change its status.');
        }

        // "completed" is only meaningful once a diagnosis exists.
        if ($data['status'] === Appointment::STATUS_COMPLETED && blank($appointment->diagnosis)) {
            return back()->with('error', 'The doctor must write the diagnosis before the visit can be completed.');
        }

        $appointment->update([
            'status'        => $data['status'],
            'cancel_reason' => $data['status'] === Appointment::STATUS_CANCELLED
                ? ($data['cancel_reason'] ?? 'Cancelled by the administration')
                : $appointment->cancel_reason,
        ]);

        ActivityLog::record('appointment.status', "Appointment #{$appointment->id} set to {$data['status']}");

        return back()->with('success', 'Appointment status is now "'.$data['status'].'".');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        if (! $appointment->canBeDeleted()) {
            return back()->with('error', 'A completed appointment is part of the medical record and cannot be deleted.');
        }

        $appointment->delete();   // soft delete

        ActivityLog::record('appointment.deleted', "Deleted appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been deleted. It can be restored.');
    }

    public function restore(int $id): RedirectResponse
    {
        $appointment = Appointment::onlyTrashed()->findOrFail($id);
        $appointment->restore();

        ActivityLog::record('appointment.restored', "Restored appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been restored.');
    }

    /**
     * Is this doctor already busy at that exact date + time?
     */
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
