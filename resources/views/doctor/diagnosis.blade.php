@extends('layouts.app')

@section('title', 'Patient Diagnosis')

@section('content')

    <div class="mb-4">
        <h3>Diagnosis</h3>
        <p class="text-muted mb-0">
            A diagnosis can only be written for an <strong>accepted</strong> appointment.
        </p>
    </div>

    @if (! $appointment)

        {{-- ---------- No appointment chosen yet: show the list ---------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h5 class="mb-3">Choose the visit you want to write about</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Patient</th><th>Date</th><th>Time</th><th>Diagnosis</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($waiting as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->patient->name }}</td>
                                    <td>{{ $item->appointment_date->format('d M Y') }}</td>
                                    <td>{{ $item->time_label }}</td>
                                    <td>
                                        @if ($item->diagnosis)
                                            <span class="badge bg-success">Written</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Not written</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('doctor.diagnosis', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Write
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-clipboard2-pulse"></i>
                                            You have no accepted appointments waiting for a diagnosis.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    @else

        {{-- ---------- The form for one appointment ---------- --}}
        <div class="row g-4">

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
                            <tr><td class="text-muted">Blood</td><td>{{ $appointment->patient->blood_group ?: 'Unknown' }}</td></tr>
                            <tr><td class="text-muted">Visit</td><td>{{ $appointment->appointment_date->format('d M Y') }}</td></tr>
                            <tr><td class="text-muted">Status</td>
                                <td><span class="badge {{ $appointment->statusBadge() }}">{{ ucfirst($appointment->status) }}</span></td>
                            </tr>
                        </table>

                    </div>
                </div>

                @if ($appointment->analyses->isNotEmpty())
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h6 class="mb-3">Uploaded Analyses</h6>
                            @foreach ($appointment->analyses as $file)
                                <a href="{{ $file->url }}" target="_blank"
                                   class="d-block border rounded p-2 mb-2 text-decoration-none">
                                    <i class="bi bi-file-earmark-text me-1"></i> {{ $file->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">

                        <h5 class="mb-4">Write Diagnosis</h5>

                        <p class="text-muted"><strong>Symptoms reported by the patient:</strong><br>
                            {{ $appointment->symptoms ?: 'The patient did not describe any symptoms.' }}</p>

                        <form method="POST" action="{{ route('doctor.diagnosis.store', $appointment) }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="diagnosis">Diagnosis <span class="text-danger">*</span></label>
                                <textarea id="diagnosis" name="diagnosis" rows="5"
                                          class="form-control @error('diagnosis') is-invalid @enderror"
                                          placeholder="Write the patient's diagnosis..."
                                          required>{{ old('diagnosis', $appointment->diagnosis) }}</textarea>
                                @error('diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="notes">Doctor Notes</label>
                                <textarea id="notes" name="notes" rows="4" class="form-control"
                                          placeholder="Add additional notes...">{{ old('notes', $appointment->notes) }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Diagnosis
                                </button>

                                <a href="{{ route('doctor.prescription', $appointment) }}" class="btn btn-outline-primary">
                                    Go to Prescription <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>

    @endif

@endsection
