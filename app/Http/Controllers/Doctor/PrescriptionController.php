<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * PHASE 4 - Medical records: Diagnosis + Prescription.
 *
 * The rule that governs this whole file:
 *   a prescription (and a diagnosis) may only be written for an appointment
 *   whose status is ACCEPTED (or one already COMPLETED, so it can be edited).
 *   A rejected or cancelled appointment can never get one.
 */
class PrescriptionController extends Controller
{
    /* ==================================================================
     | DIAGNOSIS
     | =================================================================*/

    /**
     * The Diagnosis page.
     *
     * Opened without an appointment it shows the list of visits waiting for a
     * diagnosis; opened with one it shows the form for that visit.
     */
    public function diagnosis(?Appointment $appointment = null): View
    {
        $doctor = $this->doctor();

        if ($appointment) {
            $this->authorizeOwnership($appointment);
            $appointment->load(['patient.user', 'analyses', 'prescription.medicines']);
        }

        $waiting = $doctor->appointments()
            ->with('patient.user')
            ->whereIn('status', [Appointment::STATUS_ACCEPTED, Appointment::STATUS_COMPLETED])
            ->orderByDesc('appointment_date')
            ->get();

        return view('doctor.diagnosis', compact('doctor', 'appointment', 'waiting'));
    }

    public function storeDiagnosis(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwnership($appointment);

        if (! $appointment->canHavePrescription()) {
            return back()->with('error',
                'A diagnosis can only be written for an accepted appointment. This one is '.$appointment->status.'.');
        }

        $data = $request->validate([
            'diagnosis' => ['required', 'string', 'max:2000'],
            'notes'     => ['nullable', 'string', 'max:2000'],
        ], [
            'diagnosis.required' => 'The diagnosis is required.',
        ]);

        $appointment->update([
            'diagnosis' => $data['diagnosis'],
            'notes'     => $data['notes'] ?? $appointment->notes,
        ]);

        ActivityLog::record('diagnosis.saved', "Diagnosis saved for appointment #{$appointment->id}");

        return redirect()
            ->route('doctor.prescription', $appointment)
            ->with('success', 'Diagnosis saved. You can now write the prescription.');
    }

    /* ==================================================================
     | PRESCRIPTION
     | =================================================================*/

    public function prescription(?Appointment $appointment = null): View
    {
        $doctor = $this->doctor();

        if ($appointment) {
            $this->authorizeOwnership($appointment);
            $appointment->load(['patient.user', 'prescription.medicines']);
        }

        $waiting = $doctor->appointments()
            ->with('patient.user')
            ->whereIn('status', [Appointment::STATUS_ACCEPTED, Appointment::STATUS_COMPLETED])
            ->orderByDesc('appointment_date')
            ->get();

        return view('doctor.prescription', [
            'doctor'      => $doctor,
            'appointment' => $appointment,
            'waiting'     => $waiting,
            // The doctor SELECTS from this catalogue, they cannot invent a medicine.
            'medicines'   => Medicine::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Save the prescription and mark the visit as COMPLETED.
     */
    public function storePrescription(StorePrescriptionRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwnership($appointment);

        if (! $appointment->canHavePrescription()) {
            return back()->with('error',
                'A prescription can only be created for an accepted appointment. This one is '.$appointment->status.'.');
        }

        if (blank($appointment->diagnosis)) {
            return back()->with('error', 'Please write the diagnosis before the prescription.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($appointment, $data) {
            // updateOrCreate: write a new prescription, or overwrite the existing
            // one if the doctor is editing.
            $prescription = Prescription::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id'    => $appointment->doctor_id,
                    'patient_id'   => $appointment->patient_id,
                    'instructions' => $data['instructions'] ?? null,
                ]
            );

            // Build the pivot rows: medicine_id => [dosage, frequency, duration]
            $pivot = [];
            foreach ($data['medicines'] as $row) {
                $pivot[$row['medicine_id']] = [
                    'dosage'    => $row['dosage'],
                    'frequency' => $row['frequency'],
                    'duration'  => $row['duration'],
                ];
            }

            // sync() replaces the whole list in the pivot table in one go.
            $prescription->medicines()->sync($pivot);

            // The visit is now finished.
            $appointment->update(['status' => Appointment::STATUS_COMPLETED]);
        });

        ActivityLog::record('prescription.saved', "Prescription saved for appointment #{$appointment->id}");

        return redirect()
            ->route('doctor.appointments.index')
            ->with('success', 'Prescription saved and the appointment is now completed.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     | -----------------------------------------------------------------*/

    private function doctor()
    {
        $doctor = Auth::user()->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        return $doctor;
    }

    private function authorizeOwnership(Appointment $appointment): void
    {
        abort_if($appointment->doctor_id !== $this->doctor()->id, 403,
            'This appointment belongs to another doctor.');
    }
}
