<?php

namespace App\Http\Controllers\Admin;

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
 * Full CRUD for patients. Admin only.
 */
class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search  = $request->query('search');
        $gender  = $request->query('gender');
        $status  = $request->query('status');
        $trashed = $request->boolean('trashed');

        $patients = Patient::with('user')
            ->withCount('appointments')
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($gender, fn ($q) => $q->where('gender', $gender))
            ->when($status, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status', $status)))
            ->orderBy('id')
            ->get();

        return view('admin.patients', compact('patients', 'search', 'gender', 'status', 'trashed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'password'    => ['required', 'string', 'min:8'],
            'dob'         => ['required', 'date', 'before:today'],
            'gender'      => ['required', 'in:male,female'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address'     => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
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

        ActivityLog::record('patient.created', "Added patient: {$data['name']}");

        return back()->with('success', "Patient \"{$data['name']}\" has been added.");
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($patient->user_id)],
            'phone'       => ['nullable', 'string', 'max:20'],
            'password'    => ['nullable', 'string', 'min:8'],
            'dob'         => ['required', 'date', 'before:today'],
            'gender'      => ['required', 'in:male,female'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address'     => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($patient, $data) {
            $userFields = [
                'name'   => $data['name'],
                'email'  => $data['email'],
                'phone'  => $data['phone'] ?? null,
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $userFields['password'] = $data['password'];
            }

            $patient->user->update($userFields);

            $patient->update([
                'dob'         => $data['dob'],
                'gender'      => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'address'     => $data['address'] ?? null,
            ]);
        });

        ActivityLog::record('patient.updated', "Updated patient: {$data['name']}");

        return back()->with('success', 'Patient has been updated.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->name;

        $patient->delete();                                  // soft delete
        $patient->user?->update(['status' => 'inactive']);

        ActivityLog::record('patient.deleted', "Deleted patient: {$name}");

        return back()->with('success', "Patient \"{$name}\" has been deleted. The record can be restored.");
    }

    public function restore(int $id): RedirectResponse
    {
        $patient = Patient::onlyTrashed()->findOrFail($id);
        $patient->restore();
        $patient->user?->update(['status' => 'active']);

        ActivityLog::record('patient.restored', "Restored patient: {$patient->name}");

        return back()->with('success', 'Patient has been restored.');
    }
}
