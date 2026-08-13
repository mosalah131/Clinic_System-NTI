@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="mb-4">
        <h3>Dashboard</h3>
        <p class="text-muted">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    {{-- ---------- The four main counters ---------- --}}
    <div class="row g-4">

        @foreach ([
            ['label' => 'Patients',     'value' => $stats['patients'],     'icon' => 'people',         'link' => route('admin.patients.index')],
            ['label' => 'Doctors',      'value' => $stats['doctors'],      'icon' => 'person-badge',   'link' => route('admin.doctors.index')],
            ['label' => 'Appointments', 'value' => $stats['appointments'], 'icon' => 'calendar-check', 'link' => route('admin.appointments.index')],
            ['label' => 'Departments',  'value' => $stats['departments'],  'icon' => 'building',       'link' => route('admin.departments.index')],
        ] as $card)
            <div class="col-md-6 col-xl-3">
                <a href="{{ $card['link'] }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm p-3 quick-action">
                        <div class="d-flex align-items-center gap-3">
                            <div class="card-icon">
                                <i class="bi bi-{{ $card['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                                <h3 class="mb-0">{{ $card['value'] }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach

    </div>

    {{-- ---------- Appointment breakdown ---------- --}}
    <div class="row g-3 mt-2">

        @foreach ([
            ['label' => "Today's Appointments", 'value' => $stats['today'],     'class' => 'text-primary',   'status' => ''],
            ['label' => 'Pending',              'value' => $stats['pending'],   'class' => 'text-warning',   'status' => 'pending'],
            ['label' => 'Accepted',             'value' => $stats['accepted'],  'class' => 'text-success',   'status' => 'accepted'],
            ['label' => 'Rejected',             'value' => $stats['rejected'],  'class' => 'text-danger',    'status' => 'rejected'],
            ['label' => 'Cancelled',            'value' => $stats['cancelled'], 'class' => 'text-secondary', 'status' => 'cancelled'],
            ['label' => 'Completed',            'value' => $stats['completed'], 'class' => 'text-info',      'status' => 'completed'],
        ] as $item)
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.appointments.index', array_filter(['status' => $item['status']])) }}"
                   class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm p-3 text-center stat-card quick-action">
                        <h2 class="{{ $item['class'] }} mb-1">{{ $item['value'] }}</h2>
                        <p class="mb-0 small">{{ $item['label'] }}</p>
                    </div>
                </a>
            </div>
        @endforeach

    </div>

    {{-- ---------- Today's appointments + quick actions ---------- --}}
    <div class="row g-4 mt-2">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Today's Appointments</h5>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-primary btn-sm">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse loops, and @empty runs when there is nothing to loop over. --}}
                                @forelse ($todayAppointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->patient->name }}</td>
                                        <td>{{ $appointment->doctor->display_name }}</td>
                                        <td>{{ $appointment->time_label }}</td>
                                        <td>
                                            <span class="badge {{ $appointment->statusBadge() }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="bi bi-calendar-x"></i>
                                                There are no appointments scheduled for today.
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

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-4">Quick Actions</h5>

                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-person-plus me-2"></i> Manage Patients
                        </a>
                        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-person-badge me-2"></i> Manage Doctors
                        </a>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-calendar-plus me-2"></i> Manage Appointments
                        </a>
                        <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-capsule me-2"></i> Manage Medicines
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>

@endsection
