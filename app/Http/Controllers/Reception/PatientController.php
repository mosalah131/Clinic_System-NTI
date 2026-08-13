<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PHASE 3 - Reception logic.
 *
 * Reception registers walk-in patients and edits their contact details.
 * Reception may NOT delete patients, write prescriptions or touch departments.
 */
class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $gender = $request->query('gender');
        $status = $request->query('status');

        $patients = Patient::with('user')
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($gender, fn ($q) => $q->where('gender', $gender))
            ->when($status, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', $status)))
            ->orderByDesc('id')
            ->get();

        return view('reception.patients', compact('patients', 'search', 'gender', 'status'));
    }

    /** Register a patient who arrived at the clinic without an account. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'       => ['required', 'string', 'max:20'],
            'password'    => ['required', 'string', 'min:8'],
            'dob'         => ['required', 'date', 'before:today'],
            'gender'      => ['required', 'in:male,female'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address'     => ['nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'This email is already registered.',
            'password.min' => 'The password must be at least 8 characters.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
                'password' => $data['password'],
                'role'     => User::ROLE_PATIENT,
                'status'   => 'active',
            ]);

            Patient::create([
                'user_id'     => $user->id,
                'dob'         => $data['dob'],
                'gender'      => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'address'     => $data['address'] ?? null,
            ]);
        });

        ActivityLog::record('patient.registered', "Reception registered patient: {$data['name']}");

        return back()->with('success', "Patient \"{$data['name']}\" has been registered.");
    }

    /** Edit contact details (phone, address, ...). */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($patient->user_id)],
            'phone'       => ['required', 'string', 'max:20'],
            'dob'         => ['required', 'date', 'before:today'],
            'gender'      => ['required', 'in:male,female'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address'     => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($patient, $data) {
            $patient->user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            $patient->update([
                'dob'         => $data['dob'],
                'gender'      => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'address'     => $data['address'] ?? null,
            ]);
        });

        ActivityLog::record('patient.updated', "Reception updated patient: {$data['name']}");

        return back()->with('success', 'Patient details have been updated.');
    }
}
