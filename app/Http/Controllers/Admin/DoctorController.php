<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Full CRUD for doctors. Admin only.
 *
 * Creating a doctor means creating TWO rows:
 *   1. a "users" row  (the login account, role = doctor)
 *   2. a "doctors" row (the professional profile)
 * A database transaction makes sure we never end up with only one of them.
 */
class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $search       = $request->query('search');
        $departmentId = $request->query('department_id');
        $trashed      = $request->boolean('trashed');

        $doctors = Doctor::with(['user', 'department'])
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->orderBy('id')
            ->get();

        return view('admin.doctors', [
            'doctors'      => $doctors,
            'departments'  => Department::orderBy('name')->get(),
            'search'       => $search,
            'departmentId' => $departmentId,
            'trashed'      => $trashed,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'password'         => ['required', 'string', 'min:8'],
            'department_id'    => ['required', 'exists:departments,id'],
            'specialization'   => ['required', 'string', 'max:100'],
            'consultation_fee' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'bio'              => ['nullable', 'string', 'max:1000'],
        ], [
            'email.unique'   => 'This email is already used by another account.',
            'password.min'   => 'The password must be at least 8 characters.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $data['password'],
                'role'     => User::ROLE_DOCTOR,
                'status'   => 'active',
            ]);

            Doctor::create([
                'user_id'          => $user->id,
                'department_id'    => $data['department_id'],
                'specialization'   => $data['specialization'],
                'consultation_fee' => $data['consultation_fee'],
                'bio'              => $data['bio'] ?? null,
            ]);
        });

        ActivityLog::record('doctor.created', "Added doctor: {$data['name']}");

        return back()->with('success', "Dr. {$data['name']} has been added.");
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($doctor->user_id)],
            'phone'            => ['nullable', 'string', 'max:20'],
            'password'         => ['nullable', 'string', 'min:8'],
            'department_id'    => ['required', 'exists:departments,id'],
            'specialization'   => ['required', 'string', 'max:100'],
            'consultation_fee' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'status'           => ['required', 'in:active,inactive'],
            'bio'              => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($doctor, $data) {
            $userFields = [
                'name'   => $data['name'],
                'email'  => $data['email'],
                'phone'  => $data['phone'] ?? null,
                'status' => $data['status'],
            ];

            // Only overwrite the password when a new one was actually typed.
            if (! empty($data['password'])) {
                $userFields['password'] = $data['password'];
            }

            $doctor->user->update($userFields);

            $doctor->update([
                'department_id'    => $data['department_id'],
                'specialization'   => $data['specialization'],
                'consultation_fee' => $data['consultation_fee'],
                'bio'              => $data['bio'] ?? null,
            ]);
        });

        ActivityLog::record('doctor.updated', "Updated doctor: {$data['name']}");

        return back()->with('success', 'Doctor has been updated.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $name = $doctor->name;

        // Soft delete: the doctor disappears from the lists but the medical
        // history (appointments, prescriptions) stays intact.
        $doctor->delete();
        $doctor->user?->update(['status' => 'inactive']);

        ActivityLog::record('doctor.deleted', "Deleted doctor: {$name}");

        return back()->with('success', "Dr. {$name} has been deleted. You can restore this record.");
    }

    public function restore(int $id): RedirectResponse
    {
        $doctor = Doctor::onlyTrashed()->findOrFail($id);
        $doctor->restore();
        $doctor->user?->update(['status' => 'active']);

        ActivityLog::record('doctor.restored', "Restored doctor: {$doctor->name}");

        return back()->with('success', 'Doctor has been restored.');
    }
}
