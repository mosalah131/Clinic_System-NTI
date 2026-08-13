@extends('layouts.app')

@section('title', 'Appointments')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Appointments</h3>
            <p class="text-muted mb-0">Every appointment in the clinic</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="bi bi-plus-lg"></i> Add Appointment
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Patient or doctor...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Appointment::statuses() as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Doctor</label>
                    <select name="doctor_id" class="form-select">
                        <option value="">All Doctors</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected($doctorId == $doctor->id)>
                                {{ $doctor->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

                @if ($trashed)<input type="hidden" name="trashed" value="1">@endif

            </form>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.appointments.index') }}"
           class="btn btn-sm {{ $trashed ? 'btn-outline-secondary' : 'btn-primary' }}">Active</a>
        <a href="{{ route('admin.appointments.index', ['trashed' => 1]) }}"
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
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->id }}</td>
                                <td>{{ $appointment->patient->name }}</td>
                                <td>{{ $appointment->doctor->display_name }}</td>
                                <td>{{ $appointment->doctor->department->name ?? '-' }}</td>
                                <td>{{ $appointment->appointment_date->format('d M Y') }}</td>
                                <td>{{ $appointment->time_label }}</td>

                                <td>
                                    <span class="badge {{ $appointment->statusBadge() }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($trashed)
                                        <form method="POST" action="{{ route('admin.appointments.restore', $appointment->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <div class="d-flex gap-1 flex-wrap">

                                            {{-- Accept / reject only make sense while pending --}}
                                            @if ($appointment->status === \App\Models\Appointment::STATUS_PENDING)
                                                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="accepted">
                                                    <button class="btn btn-sm btn-outline-success" title="Accept">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-sm btn-outline-danger" title="Reject">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($appointment->canBeEdited())
                                                <button class="btn btn-sm btn-outline-primary" title="Edit"
                                                        data-bs-toggle="modal" data-bs-target="#editAppointmentModal"
                                                        data-fill-modal="editAppointmentModal"
                                                        data-id="{{ $appointment->id }}"
                                                        data-doctorid="{{ $appointment->doctor_id }}"
                                                        data-date="{{ $appointment->appointment_date->toDateString() }}"
                                                        data-time="{{ substr($appointment->appointment_time, 0, 5) }}"
                                                        data-notes="{{ $appointment->notes }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endif

                                            @if ($appointment->canBeCancelled())
                                                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}"
                                                      data-confirm="Cancel appointment #{{ $appointment->id }}?">
                                                    @csrf
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Cancel">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($appointment->canBeDeleted())
                                                <form method="POST" action="{{ route('admin.appointments.destroy', $appointment) }}"
                                                      data-confirm="Delete appointment #{{ $appointment->id }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        No appointments match your filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= Add appointment ================= --}}
    <div class="modal fade" id="addAppointmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.appointments.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Create New Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}">
                                            {{ $patient->user->name }} ({{ $patient->user->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Doctor</label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">Select Doctor</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">
                                            {{ $doctor->display_name }} - {{ $doctor->department->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="appointment_date" class="form-control"
                                       min="{{ now()->toDateString() }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <select name="appointment_time" class="form-select" required>
                                    <option value="">Select Time</option>
                                    @foreach (config('clinic.time_slots') as $slot)
                                        <option value="{{ $slot }}">{{ date('h:i A', strtotime($slot)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Symptoms / Reason</label>
                                <textarea name="symptoms" rows="2" class="form-control"
                                          placeholder="Why is the patient coming?"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="2" class="form-control"
                                          placeholder="Appointment notes..."></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Appointment</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= Edit appointment ================= --}}
    <div class="modal fade" id="editAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST"
                      data-action-template="{{ route('admin.appointments.update', '__ID__') }}"
                      action="{{ route('admin.appointments.update', '__ID__') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label">Doctor</label>
                                <select name="doctor_id" data-field="doctorid" class="form-select" required>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="appointment_date" data-field="date" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <select name="appointment_time" data-field="time" class="form-select" required>
                                    @foreach (config('clinic.time_slots') as $slot)
                                        <option value="{{ $slot }}">{{ date('h:i A', strtotime($slot)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" data-field="notes" rows="2" class="form-control"></textarea>
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
