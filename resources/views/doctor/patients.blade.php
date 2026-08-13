@extends('layouts.app')

@section('title', 'My Patients')

@section('content')

    <div class="mb-4">
        <h3>My Patients</h3>
        <p class="text-muted mb-0">Everyone who has had an appointment with you</p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('doctor.patients.index') }}" class="row g-3">

                <div class="col-md-9">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search by patient name or phone...">
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                    <a href="{{ route('doctor.patients.index') }}" class="btn btn-secondary w-100">Reset</a>
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
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Phone</th>
                            <th>Last Appointment</th>
                            <th>Action</th>
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

                                <td>
                                    {{ $patient->last_appointment
                                        ? \Carbon\Carbon::parse($patient->last_appointment)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    <a href="{{ route('doctor.patients.show', $patient) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-folder2-open"></i> History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-people"></i>
                                        You have not treated any patients yet.
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
