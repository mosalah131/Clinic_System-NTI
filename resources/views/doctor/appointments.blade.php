@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')

    <div class="mb-4">
        <h3>My Appointments</h3>
        <p class="text-muted mb-0">Accept or reject the requests, then write the diagnosis</p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('doctor.appointments.index') }}" class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Search Patient</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search patient name...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Appointment::statuses() as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Symptoms</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->id }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle fs-4"></i>
                                        <div>
                                            <strong>{{ $appointment->patient->name }}</strong>
                                            <small class="d-block text-muted">
                                                {{ $appointment->patient->age }} years &middot;
                                                {{ ucfirst($appointment->patient->gender) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $appointment->appointment_date->format('d M Y') }}</td>
                                <td>{{ $appointment->time_label }}</td>
                                <td>{{ Str::limit($appointment->symptoms, 30) ?: '-' }}</td>

                                <td>
                                    <span class="badge {{ $appointment->statusBadge() }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex gap-1 flex-wrap">

                                        <a href="{{ route('doctor.appointments.show', $appointment) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Open">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Only a PENDING request can be accepted or rejected --}}
                                        @if ($appointment->canBeReviewed())
                                            <form method="POST" action="{{ route('doctor.appointments.accept', $appointment) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-lg"></i> Accept
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('doctor.appointments.reject', $appointment) }}"
                                                  data-confirm="Reject this appointment?">
                                                @csrf
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Once accepted, the medical work can start --}}
                                        @if ($appointment->canHavePrescription())
                                            <a href="{{ route('doctor.diagnosis', $appointment) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-clipboard2-pulse"></i>
                                                {{ $appointment->diagnosis ? 'Edit Diagnosis' : 'Diagnosis' }}
                                            </a>

                                            <a href="{{ route('doctor.prescription', $appointment) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-medical"></i>
                                                {{ $appointment->prescription ? 'Edit Rx' : 'Prescription' }}
                                            </a>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
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

@endsection
