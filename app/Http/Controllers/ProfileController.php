<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "My Profile" / "Settings" for every role.
 *
 * One controller serves all four roles: the GET methods pick the right Blade
 * view, and the two POST methods (update / password) are shared.
 */
class ProfileController extends Controller
{
    /* ==================================================================
     | The four profile pages
     | =================================================================*/

    public function adminSettings(): View
    {
        return view('admin.settings', ['user' => Auth::user()]);
    }

    public function doctorProfile(): View
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        abort_if(! $doctor, 403, 'Your doctor profile has not been created yet.');

        $stats = [
            'patients'      => $doctor->appointments()->distinct('patient_id')->count('patient_id'),
            'appointments'  => $doctor->appointments()->count(),
            'completed'     => $doctor->appointments()->where('status', Appointment::STATUS_COMPLETED)->count(),
        ];

        return view('doctor.profile', [
            'user'        => $user,
            'doctor'      => $doctor,
            'departments' => Department::orderBy('name')->get(),
            'stats'       => $stats,
        ]);
    }

    public function patientProfile(): View
    {
        $user    = Auth::user();
        $patient = $user->patient;

        abort_if(! $patient, 403, 'Your patient profile has not been created yet.');

        return view('patient.profile', compact('user', 'patient'));
    }

    public function receptionProfile(): View
    {
        return view('reception.profile', ['user' => Auth::user()]);
    }

    /* ==================================================================
     | Saving the profile
     | =================================================================*/

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Fields everybody has.
        $rules = [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        // Extra fields depending on who is saving.
        if ($user->isDoctor()) {
            $rules += [
                'department_id'    => ['required', 'exists:departments,id'],
                'specialization'   => ['required', 'string', 'max:100'],
                'consultation_fee' => ['required', 'numeric', 'min:0', 'max:99999.99'],
                'bio'              => ['nullable', 'string', 'max:1000'],
            ];
        }

        if ($user->isPatient()) {
            $rules += [
                'dob'         => ['required', 'date', 'before:today'],
                'gender'      => ['required', 'in:male,female'],
                'blood_group' => ['nullable', 'string', 'max:5'],
                'address'     => ['nullable', 'string', 'max:255'],
            ];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if ($user->isDoctor() && $user->doctor) {
                $user->doctor->update([
                    'department_id'    => $data['department_id'],
                    'specialization'   => $data['specialization'],
                    'consultation_fee' => $data['consultation_fee'],
                    'bio'              => $data['bio'] ?? null,
                ]);
            }

            if ($user->isPatient() && $user->patient) {
                $user->patient->update([
                    'dob'         => $data['dob'],
                    'gender'      => $data['gender'],
                    'blood_group' => $data['blood_group'] ?? null,
                    'address'     => $data['address'] ?? null,
                ]);
            }
        });

        ActivityLog::record('profile.updated', $user->name.' updated their profile.');

        return back()->with('success', 'Your profile has been saved.');
    }

    /* ==================================================================
     | Changing the password
     | =================================================================*/

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min'       => 'The new password must be at least 8 characters.',
            'password.confirmed' => 'The two new passwords do not match.',
        ]);

        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is not correct.',
            ]);
        }

        $user->update(['password' => $data['password']]);

        ActivityLog::record('profile.password', $user->name.' changed their password.');

        return back()->with('success', 'Your password has been changed.');
    }
}
