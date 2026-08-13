@extends('layouts.app')

@section('title', 'Doctor Dashboard')

@section('content')

    <div class="mb-4">
        <h3>Welcome, {{ $doctor->display_name }}</h3>
        <p class="text-muted mb-0">
            {{ $doctor->specialization }} &middot; {{ $doctor->department->name ?? 'No department' }}
        </p>
    </div>

    <div class="row g-4">

        @foreach ([
            ['label' => "Today's Appointments", 'value' => $stats['today'],     'icon' => 'calendar-day',   'class' => 'text-primary'],
            ['label' => 'Pending Requests',     'value' => $stats['pending'],   'icon' => 'hourglass-split','class' => 'text-warning'],
            ['label' => 'Completed Visits',     'value' => $stats['completed'], 'icon' => 'check2-circle',  'class' => 'text-success'],
            ['label' => 'My Patients',          'value' => $stats['patients'],  'icon' => 'people',         'class' => 'text-info'],
        ] as $card)
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm p-3 stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon"><i class="bi bi-{{ $card['icon'] }}"></i></div>
                        <div>
                            <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                            <h2 class="mb-0 {{ $card['class'] }}">{{ $card['value'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="row g-4 mt-2">

        {{-- ---------- Today's schedule ---------- --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Today's Appointments</h5>
                        <a href="{{ route('doctor.appointments.index') }}" class="btn btn-primary btn-sm">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Time</th>
                                    <th>Symptoms</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($todayAppointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->patient->name }}</td>
                                        <td>{{ $appointment->time_label }}</td>
                                        <td>{{ Str::limit($appointment->symptoms, 30) ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $appointment->statusBadge() }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('doctor.appointments.show', $appointment) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Open
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="bi bi-calendar-check"></i>
                                                You have no appointments today.
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

        {{-- ---------- Recent prescriptions ---------- --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-3">Recent Prescriptions</h5>

                    @forelse ($recentPrescriptions as $prescription)
                        <div class="border rounded p-3 mb-2">
                            <strong>{{ $prescription->patient->name }}</strong>
                            <small class="d-block text-muted">
                                {{ Str::limit($prescription->appointment->diagnosis ?? 'No diagnosis', 40) }}
                            </small>
                            <small class="text-muted">{{ $prescription->created_at->format('d M Y') }}</small>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-file-medical"></i>
                            You have not written any prescriptions yet.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

    </div>

@endsection
