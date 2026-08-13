<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnalysisRequest;
use App\Models\ActivityLog;
use App\Models\Analysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * PHASE 4 - Analysis Upload Module.
 *
 * The patient uploads a blood test, an x-ray ... The FILE goes into
 * storage/app/public/analyses/ and only the PATH is saved in the database.
 * The doctor then sees it when opening the appointment.
 */
class AnalysisController extends Controller
{
    public function index(): View
    {
        $patient = $this->patient();

        $analyses = $patient->analyses()
            ->with('appointment.doctor.user')
            ->latest()
            ->get();

        // The patient can attach the file to one of their own appointments.
        $appointments = $patient->appointments()
            ->with('doctor.user')
            ->orderByDesc('appointment_date')
            ->get();

        return view('patient.analysis', compact('patient', 'analyses', 'appointments'));
    }

    public function store(StoreAnalysisRequest $request): RedirectResponse
    {
        $patient = $this->patient();

        // StoreAnalysisRequest already checked the title, the file extension
        // and the file size before this method started.
        $data = $request->validated();

        // Never let a patient attach a file to somebody else's appointment.
        if (! empty($data['appointment_id'])) {
            abort_if(
                ! $patient->appointments()->where('id', $data['appointment_id'])->exists(),
                403,
                'That appointment does not belong to you.'
            );
        }

        $uploaded = $request->file('file');
        $path     = $uploaded->store('analyses', 'public');

        Analysis::create([
            'patient_id'     => $patient->id,
            'appointment_id' => $data['appointment_id'] ?? null,
            'title'          => $data['title'],
            'file_name'      => $uploaded->getClientOriginalName(),
            'file_path'      => $path,
            'file_type'      => $data['file_type'],
            'description'    => $data['description'] ?? null,
        ]);

        ActivityLog::record('analysis.uploaded', "Patient uploaded analysis: {$data['title']}");

        return back()->with('success', 'Your file has been uploaded successfully.');
    }

    public function destroy(Analysis $analysis): RedirectResponse
    {
        $patient = $this->patient();

        abort_if($analysis->patient_id !== $patient->id, 403, 'This file is not yours.');

        // Remove the real file from the disk as well, not just the database row.
        Storage::disk('public')->delete($analysis->file_path);
        $analysis->delete();

        ActivityLog::record('analysis.deleted', "Patient deleted analysis: {$analysis->title}");

        return back()->with('success', 'The file has been deleted.');
    }

    private function patient()
    {
        $patient = Auth::user()->patient;

        abort_if(! $patient, 403, 'Your patient profile has not been created yet.');

        return $patient;
    }
}
