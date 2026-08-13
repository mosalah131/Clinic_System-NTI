<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MedicalFile;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * "Patient Files" - the doctor uploads a report, an x-ray, a scan ...
 *
 * The file itself is stored in  storage/app/public/medical-files/
 * and only its PATH is written into the database.
 */
class MedicalFileController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->doctor();

        // The doctor may only attach files to their own patients.
        $patients = Patient::with('user')
            ->whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->get();

        $files = MedicalFile::with(['patient.user', 'uploader'])
            ->whereIn('patient_id', $patients->pluck('id'))
            ->latest()
            ->get();

        return view('doctor.files', compact('doctor', 'patients', 'files'));
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor    = $this->doctor();
        $maxKb     = config('clinic.uploads.max_size_kb');
        $allowed   = implode(',', config('clinic.uploads.medical_file_extensions'));

        $data = $request->validate([
            'patient_id'  => ['required', 'exists:patients,id'],
            'title'       => ['required', 'string', 'max:150'],
            'file_type'   => ['required', 'in:lab_result,x_ray,prescription_scan,other'],
            'description' => ['nullable', 'string', 'max:500'],
            'file'        => ['required', 'file', "mimes:{$allowed}", "max:{$maxKb}"],
        ], [
            'file.required' => 'Please choose a file to upload.',
            'file.mimes'    => 'Allowed file types are: '.strtoupper(str_replace(',', ', ', $allowed)).'.',
            'file.max'      => 'The file is too large. The maximum size is '.round($maxKb / 1024).' MB.',
        ]);

        // Security: is this really one of my patients?
        abort_if(
            ! Patient::where('id', $data['patient_id'])
                ->whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
                ->exists(),
            403,
            'You can only upload files for your own patients.'
        );

        // store() generates a unique name so two files never overwrite each other.
        $path = $request->file('file')->store('medical-files', 'public');

        MedicalFile::create([
            'patient_id'  => $data['patient_id'],
            'uploaded_by' => Auth::id(),
            'title'       => $data['title'],
            'file_path'   => $path,
            'file_type'   => $data['file_type'],
            'description' => $data['description'] ?? null,
        ]);

        ActivityLog::record('medical_file.uploaded', "Uploaded file: {$data['title']}");

        return back()->with('success', 'The file has been uploaded.');
    }

    public function destroy(MedicalFile $medicalFile): RedirectResponse
    {
        // Only the person who uploaded the file may remove it.
        abort_if($medicalFile->uploaded_by !== Auth::id(), 403, 'You did not upload this file.');

        Storage::disk('public')->delete($medicalFile->file_path);
        $medicalFile->delete();

        ActivityLog::record('medical_file.deleted', "Deleted file: {$medicalFile->title}");

        return back()->with('success', 'The file has been deleted.');
    }

    private function doctor()
    {
        $doctor = Auth::user()->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        return $doctor;
    }
}
