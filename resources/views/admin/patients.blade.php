@extends('layouts.app')

@section('title', 'Patients')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Patients</h3>
            <p class="text-muted mb-0">Manage clinic patients</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
            <i class="bi bi-plus-lg"></i> Add Patient
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.patients.index') }}" class="row g-3">

                <div class="col-md-5">
                    <label class="form-label">Search Patient</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Name, email or phone...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">All</option>
                        <option value="male"   @selected($gender === 'male')>Male</option>
                        <option value="female" @selected($gender === 'female')>Female</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active"   @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

                @if ($trashed)<input type="hidden" name="trashed" value="1">@endif

            </form>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.patients.index') }}"
           class="btn btn-sm {{ $trashed ? 'btn-outline-secondary' : 'btn-primary' }}">Active</a>
        <a href="{{ route('admin.patients.index', ['trashed' => 1]) }}"
           class="btn btn-sm {{ $trashed ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-trash"></i> Deleted
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Phone</th>
                            <th>Visits</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($patients as $index => $patient)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle fs-3"></i>
                                        <div>
                                            <strong>{{ $patient->user->name }}</strong>
                                            <small class="d-block text-muted">{{ $patient->user->email }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ ucfirst($patient->gender) }}</td>
                                <td>{{ $patient->age }}</td>
                                <td>{{ $patient->user->phone ?: '-' }}</td>
                                <td>{{ $patient->appointments_count }}</td>

                                <td>
                                    <span class="badge {{ $patient->user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($patient->user->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($trashed)
                                        <form method="POST" action="{{ route('admin.patients.restore', $patient->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editPatientModal"
                                                    data-fill-modal="editPatientModal"
                                                    data-id="{{ $patient->id }}"
                                                    data-name="{{ $patient->user->name }}"
                                                    data-email="{{ $patient->user->email }}"
                                                    data-phone="{{ $patient->user->phone }}"
                                                    data-dob="{{ $patient->dob?->toDateString() }}"
                                                    data-gender="{{ $patient->gender }}"
                                                    data-blood="{{ $patient->blood_group }}"
                                                    data-address="{{ $patient->address }}"
                                                    data-status="{{ $patient->user->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.patients.destroy', $patient) }}"
                                                  data-confirm="Delete patient {{ $patient->user->name }}? The record can be restored.">
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
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-people"></i>
                                        {{ $trashed ? 'There are no deleted patients.' : 'No patients match your search.' }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= Add patient ================= --}}
    <div class="modal fade" id="addPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.patients.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Add Patient</h5>
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
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control"
                                       max="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">Unknown</option>
                                    @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $blood)
                                        <option value="{{ $blood }}">{{ $blood }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" placeholder="Patient address">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Patient</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= Edit patient ================= --}}
    <div class="modal fade" id="editPatientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST"
                      data-action-template="{{ route('admin.patients.update', '__ID__') }}"
                      action="{{ route('admin.patients.update', '__ID__') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Patient</h5>
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
                                <label class="form-label">New Password <span class="text-muted">(optional)</span></label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" data-field="dob" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" data-field="gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" data-field="blood" class="form-select">
                                    <option value="">Unknown</option>
                                    @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $blood)
                                        <option value="{{ $blood }}">{{ $blood }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" data-field="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" data-field="address" class="form-control">
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
