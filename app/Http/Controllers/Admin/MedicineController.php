<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicineRequest;
use App\Models\ActivityLog;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PHASE 4 - Medicines Module.
 *
 * Only the admin may add / edit / delete medicines. The doctor can only
 * SELECT from this catalogue when writing a prescription.
 */
class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->query('search');
        $category = $request->query('category');
        $status   = $request->query('status');
        $trashed  = $request->boolean('trashed');

        $medicines = Medicine::query()
            ->when($trashed, fn ($q) => $q->onlyTrashed())
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('name')
            ->get();

        // The category list is built from the data itself, so it always matches.
        $categories = Medicine::withTrashed()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.medicines', compact('medicines', 'categories', 'search', 'category', 'status', 'trashed'));
    }

    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $medicine = Medicine::create($request->validated());

        ActivityLog::record('medicine.created', "Added medicine: {$medicine->name}");

        return back()->with('success', "Medicine \"{$medicine->name}\" has been added.");
    }

    public function update(StoreMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($request->validated());

        ActivityLog::record('medicine.updated', "Updated medicine: {$medicine->name}");

        return back()->with('success', 'Medicine has been updated.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->delete();   // soft delete - old prescriptions keep working

        ActivityLog::record('medicine.deleted', "Deleted medicine: {$medicine->name}");

        return back()->with('success', "\"{$medicine->name}\" has been deleted. You can restore it.");
    }

    public function restore(int $id): RedirectResponse
    {
        $medicine = Medicine::onlyTrashed()->findOrFail($id);
        $medicine->restore();

        ActivityLog::record('medicine.restored', "Restored medicine: {$medicine->name}");

        return back()->with('success', 'Medicine has been restored.');
    }
}
