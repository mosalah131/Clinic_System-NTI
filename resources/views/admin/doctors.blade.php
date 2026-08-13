@extends('layouts.app')

@section('title', 'Doctors')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Doctors</h3>
            <p class="text-muted mb-0">Manage clinic doctors</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
            <i class="bi bi-plus-lg"></i> Add Doctor
        </button>
    </div>

    {{-- ---------- Search & filter (done on the server) ---------- --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.doctors.index') }}" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Search Doctor</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search by name...">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($departmentId == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

                @if ($trashed)<input type="hidden" name="trashed" value="1">@endif

            </form>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.doctors.index') }}"
           class="btn btn-sm {{ $trashed ? 'btn-outline-secondary' : 'btn-primary' }}">Active</a>
        <a href="{{ route('admin.doctors.index', ['trashed' => 1]) }}"
           class="btn btn-sm {{ $trashed ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-trash"></i> Deleted
        </a>
    </div>

    {{-- ---------- Table ---------- --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($doctors as $index => $doctor)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle fs-3"></i>
                                        <strong>{{ $doctor->display_name }}</strong>
                                    </div>
                                </td>

                                <td>{{ $doctor->department->name ?? '-' }}</td>
                                <td>{{ $doctor->specialization }}</td>
                                <td>{{ $doctor->user->email }}</td>
                                <td>{{ $doctor->user->phone ?: '-' }}</td>
                                <td>{{ number_format($doctor->consultation_fee, 2) }}</td>

                                <td>
                                    <span class="badge {{ $doctor->user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($doctor->user->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($trashed)
                                        <form method="POST" action="{{ route('admin.doctors.restore', $doctor->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editDoctorModal"
                                                    data-fill-modal="editDoctorModal"
                                                    data-id="{{ $doctor->id }}"
                                                    data-name="{{ $doctor->user->name }}"
                                                    data-email="{{ $doctor->user->email }}"
                                                    data-phone="{{ $doctor->user->phone }}"
                                                    data-departmentid="{{ $doctor->department_id }}"
                                                    data-specialization="{{ $doctor->specialization }}"
                                                    data-fee="{{ $doctor->consultation_fee }}"
                                                    data-status="{{ $doctor->user->status }}"
                                                    data-bio="{{ $doctor->bio }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}"
                                                  data-confirm="Delete {{ $doctor->display_name }}? The record can be restored later.">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="bi bi-person-badge"></i>
                                        {{ $trashed ? 'There are no deleted doctors.' : 'No doctors match your search.' }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= Add doctor ================= --}}
    <div class="modal fade" id="addDoctorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.doctors.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Add Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control"
                                       placeholder="At least 8 characters" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" class="form-control"
                                       placeholder="e.g. Heart Surgery" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Consultation Fee</label>
                                <input type="number" step="0.01" min="0" name="consultation_fee"
                                       class="form-control" value="200.00" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" rows="2" class="form-control"
                                          placeholder="Short professional summary"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Doctor</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= Edit doctor ================= --}}
    <div class="modal fade" id="editDoctorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST"
                      data-action-template="{{ route('admin.doctors.update', '__ID__') }}"
                      action="{{ route('admin.doctors.update', '__ID__') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" data-field="name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" data-field="email" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" data-field="phone" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">New Password <span class="text-muted">(leave empty to keep)</span></label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select name="department_id" data-field="departmentid" class="form-select" required>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" data-field="specialization" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Consultation Fee</label>
                                <input type="number" step="0.01" min="0" name="consultation_fee"
                                       data-field="fee" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" data-field="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" data-field="bio" rows="2" class="form-control"></textarea>
                            </div>

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
