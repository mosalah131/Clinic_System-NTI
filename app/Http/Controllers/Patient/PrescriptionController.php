<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * "My Prescriptions" - read only.
 *
 * The patient sees the diagnosis, the medicines and the doctor's instructions
 * for every completed visit.
 */
class PrescriptionController extends Controller
{
    public function index(): View
    {
        $patient = Auth::user()->patient;

        abort_if(! $patient, 403, 'Your patient profile has not been created yet.');

        $prescriptions = $patient->prescriptions()
            ->with(['doctor.user', 'doctor.department', 'appointment', 'medicines'])
            ->latest()
            ->get();

        return view('patient.prescriptions', compact('patient', 'prescriptions'));
    }
}
