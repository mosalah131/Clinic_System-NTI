@extends('layouts.app')

@section('title', 'Appointment #' . $appointment->id)

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Appointment #{{ $appointment->id }}</h3>
            <p class="text-muted mb-0">
                {{ $appointment->appointment_date->format('d M Y') }} at {{ $appointment->time_label }}
                <span class="badge {{ $appointment->statusBadge() }} ms-2">{{ ucfirst($appointment->status) }}</span>
            </p>
        </div>

        <a href="{{ route('doctor.appointments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">

        {{-- ---------- Patient information ---------- --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">

                    <div class="profile-avatar"><i class="bi bi-person"></i></div>

                    <h5>{{ $appointment->patient->name }}</h5>
                    <p class="text-muted mb-4">{{ $appointment->patient->user->email }}</p>

                    <table class="table text-start mb-0">
                        <tr><td class="text-muted">Age</td><td>{{ $appointment->patient->age }} years</td></tr>
                        <tr><td class="text-muted">Gender</td><td>{{ ucfirst($appointment->patient->gender) }}</td></tr>
                        <tr><td class="text-muted">Phone</td><td>{{ $appointment->patient->user->phone ?: '-' }}</td></tr>
                        <tr><td class="text-muted">Blood group</td><td>{{ $appointment->patient->blood_group ?: 'Unknown' }}</td></tr>
                        <tr><td class="text-muted">Address</td><td>{{ $appointment->patient->address ?: '-' }}</td></tr>
                    </table>

                </div>
            </div>
        </div>

        <div class="col-lg-8">

            {{-- ---------- Visit ---------- --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="mb-3">Visit Details</h5>

                    <p class="mb-2"><strong>Symptoms:</strong> {{ $appointment->symptoms ?: 'Not provided' }}</p>
                    <p class="mb-2"><strong>Diagnosis:</strong> {{ $appointment->diagnosis ?: 'Not written yet' }}</p>
                    <p class="mb-3"><strong>Notes:</strong> {{ $appointment->notes ?: '-' }}</p>

                    @if ($appointment->cancel_reason)
                        <div class="alert alert-warning mb-3">
                            <strong>Reason:</strong> {{ $appointment->cancel_reason }}
                        </div>
                    @endif

                    <div class="d-flex gap-2 flex-wrap">

                        @if ($appointment->canBeReviewed())
                            <form method="POST" action="{{ route('doctor.appointments.accept', $appointment) }}">
                                @csrf
                                <button class="btn btn-success"><i class="bi bi-check-lg"></i> Accept</button>
                            </form>

                            <form method="POST" action="{{ route('doctor.appointments.reject', $appointment) }}"
                                  data-confirm="Reject this appointment?">
                                @csrf
                                <button class="btn btn-danger"><i class="bi bi-x-lg"></i> Reject</button>
                            </form>
                        @endif

                        @if ($appointment->canHavePrescription())
                            <a href="{{ route('doctor.diagnosis', $appointment) }}" class="btn btn-primary">
                                <i class="bi bi-clipboard2-pulse"></i> Write Diagnosis
                            </a>

                            <a href="{{ route('doctor.prescription', $appointment) }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-medical"></i> Write Prescription
                            </a>
                        @endif

                    </div>

                </div>
            </div>

            {{-- ---------- Prescription ---------- --}}
            @if ($appointment->prescription)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <h5 class="mb-3">Prescription</h5>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointment->prescription->medicines as $medicine)
                                        <tr>
                                            <td>{{ $medicine->name }}</td>
                                            <td>{{ $medicine->pivot->dosage }}</td>
                                            <td>{{ $medicine->pivot->frequency }}</td>
                                            <td>{{ $medicine->pivot->duration }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($appointment->prescription->instructions)
                            <p class="mb-0"><strong>Instructions:</strong>
                                {{ $appointment->prescription->instructions }}</p>
                        @endif

                    </div>
                </div>
            @endif

            {{-- ---------- Analyses uploaded by the patient ---------- --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-3">Medical Analyses Uploaded by the Patient</h5>

                    @php
                        // Files attached to THIS appointment, plus everything else the
                        // patient uploaded, so the doctor has the full picture.
                        $files = $appointment->patient->analyses;
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr><th>File</th><th>Type</th><th>For</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($files as $file)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            {{ $file->title }}
                                            <small class="d-block text-muted">{{ $file->file_name }}</small>
                                        </td>
                                        <td>{{ $file->type_label }}</td>
                                        <td>
                                            @if ($file->appointment_id === $appointment->id)
                                                <span class="badge bg-primary">This visit</span>
                                            @else
                                                <span class="text-muted">Other</span>
                                            @endif
                                        </td>
                                        <td>{{ $file->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ $file->url }}" target="_blank"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="bi bi-file-earmark-medical"></i>
                                                This patient has not uploaded any analyses.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
