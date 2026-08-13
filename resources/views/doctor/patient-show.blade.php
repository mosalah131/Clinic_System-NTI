@extends('layouts.app')

@section('title', 'Patient History')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>{{ $patient->name }}</h3>
            <p class="text-muted mb-0">Complete history with you</p>
        </div>

        <a href="{{ route('doctor.patients.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">

                    <div class="profile-avatar"><i class="bi bi-person"></i></div>

                    <h5>{{ $patient->name }}</h5>
                    <p class="text-muted mb-4">{{ $patient->user->email }}</p>

                    <table class="table text-start mb-0">
                        <tr><td class="text-muted">Age</td><td>{{ $patient->age }} years</td></tr>
                        <tr><td class="text-muted">Gender</td><td>{{ ucfirst($patient->gender) }}</td></tr>
                        <tr><td class="text-muted">Phone</td><td>{{ $patient->user->phone ?: '-' }}</td></tr>
                        <tr><td class="text-muted">Blood group</td><td>{{ $patient->blood_group ?: 'Unknown' }}</td></tr>
                        <tr><td class="text-muted">Address</td><td>{{ $patient->address ?: '-' }}</td></tr>
                        <tr><td class="text-muted">Visits with you</td><td>{{ $appointments->count() }}</td></tr>
                    </table>

                </div>
            </div>

            @if ($patient->analyses->isNotEmpty())
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3">Uploaded Analyses</h6>
                        @foreach ($patient->analyses as $file)
                            <a href="{{ $file->url }}" target="_blank"
                               class="d-block border rounded p-2 mb-2 text-decoration-none">
                                <i class="bi bi-file-earmark-text me-1"></i> {{ $file->title }}
                                <small class="d-block text-muted">{{ $file->created_at->format('d M Y') }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            @forelse ($appointments as $appointment)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1">
                                    Appointment #{{ $appointment->id }} &middot;
                                    {{ $appointment->appointment_date->format('d M Y') }}
                                    at {{ $appointment->time_label }}
                                </h6>
                                <span class="badge {{ $appointment->statusBadge() }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>

                            <a href="{{ route('doctor.appointments.show', $appointment) }}"
                               class="btn btn-sm btn-outline-secondary">Open</a>
                        </div>

                        <p class="mb-1"><strong>Symptoms:</strong> {{ $appointment->symptoms ?: '-' }}</p>
                        <p class="mb-1"><strong>Diagnosis:</strong> {{ $appointment->diagnosis ?: 'Not written' }}</p>

                        @if ($appointment->prescription)
                            <p class="mb-1"><strong>Medicines:</strong>
                                {{ $appointment->prescription->medicines->pluck('name')->join(', ') }}</p>
                        @endif

                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        No appointments found.
                    </div>
                </div>
            @endforelse
        </div>

    </div>

@endsection
