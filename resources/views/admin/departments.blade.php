@extends('layouts.app')

@section('title', 'Departments')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Departments</h3>
            <p class="text-muted mb-0">Manage clinic departments</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
            <i class="bi bi-plus-lg"></i> Add Department
        </button>
    </div>

    {{-- ---------- Search ---------- --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.departments.index') }}" class="row g-3">

                <div class="col-md-8">
                    <label class="form-label">Search Department</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search department...">
                    </div>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

                {{-- Keeps the "show deleted" state while searching --}}
                @if ($trashed)<input type="hidden" name="trashed" value="1">@endif

            </form>
        </div>
    </div>

    {{-- ---------- Deleted / active switch (soft delete, Phase 5) ---------- --}}
    <div class="mb-3">
        <a href="{{ route('admin.departments.index') }}"
           class="btn btn-sm {{ $trashed ? 'btn-outline-secondary' : 'btn-primary' }}">Active</a>
        <a href="{{ route('admin.departments.index', ['trashed' => 1]) }}"
           class="btn btn-sm {{ $trashed ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-trash"></i> Deleted
        </a>
    </div>

    {{-- ---------- The cards ---------- --}}
    <div class="row g-4">

        @forelse ($departments as $department)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="card-icon"><i class="bi bi-building"></i></div>

                            <div class="d-flex gap-1">
                                @if ($trashed)
                                    <form method="POST" action="{{ route('admin.departments.restore', $department->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#editDepartmentModal"
                                            data-fill-modal="editDepartmentModal"
                                            data-id="{{ $department->id }}"
                                            data-name="{{ $department->name }}"
                                            data-description="{{ $department->description }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}"
                                          data-confirm="Delete the department &quot;{{ $department->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <h5>{{ $department->name }}</h5>
                        <p class="text-muted small mb-3">
                            {{ $department->description ?: 'No description provided.' }}
                        </p>

                        <span class="badge bg-light text-dark">
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $department->doctors_count }} {{ Str::plural('doctor', $department->doctors_count) }}
                        </span>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="empty-state">
                        <i class="bi bi-building"></i>
                        {{ $trashed ? 'There are no deleted departments.' : 'No departments found. Add the first one.' }}
                    </div>
                </div>
            </div>
        @endforelse

    </div>

    {{-- ================= Add modal ================= --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.departments.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Add Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g. Cardiology">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"
                                      placeholder="What does this department treat?"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Department</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= Edit modal ================= --}}
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                {{-- The __ID__ in the action is replaced by script.js with the real row id --}}
                <form method="POST"
                      data-action-template="{{ route('admin.departments.update', '__ID__') }}"
                      action="{{ route('admin.departments.update', '__ID__') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="name" data-field="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" data-field="description" rows="3" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
