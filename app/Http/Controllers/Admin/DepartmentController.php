<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Full CRUD for clinic departments. Admin only.
 */
class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $search  = $request->query('search');
        $trashed = $request->boolean('trashed');

        $departments = Department::withCount('doctors')
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('admin.departments', compact('departments', 'search', 'trashed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'The department name is required.',
            'name.unique'   => 'A department with this name already exists.',
        ]);

        $department = Department::create($data);

        ActivityLog::record('department.created', "Added department: {$department->name}");

        return back()->with('success', "Department \"{$department->name}\" has been added.");
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $department->update($data);

        ActivityLog::record('department.updated', "Updated department: {$department->name}");

        return back()->with('success', 'Department has been updated.');
    }

    /**
     * SOFT DELETE - the row stays in the database with a "deleted_at" date,
     * it simply stops appearing in the system.
     */
    public function destroy(Department $department): RedirectResponse
    {
        if ($department->doctors()->exists()) {
            return back()->with('error', 'This department still has doctors. Move them first.');
        }

        $department->delete();

        ActivityLog::record('department.deleted', "Deleted department: {$department->name}");

        return back()->with('success', 'Department has been deleted. You can restore it from the deleted list.');
    }

    /** Bring a soft-deleted department back. */
    public function restore(int $id): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();

        ActivityLog::record('department.restored', "Restored department: {$department->name}");

        return back()->with('success', 'Department has been restored.');
    }
}
