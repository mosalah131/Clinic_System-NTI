@extends('layouts.app')

@section('title', 'Patient Prescription')

@section('content')

    <div class="mb-4">
        <h3>Prescription</h3>
        <p class="text-muted mb-0">
            Choose the medicines from the clinic catalogue. Saving marks the visit as <strong>completed</strong>.
        </p>
    </div>

    @if (! $appointment)

        {{-- ---------- Pick an appointment ---------- --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h5 class="mb-3">Choose the visit you want to prescribe for</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Patient</th><th>Date</th><th>Diagnosis</th><th>Prescription</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($waiting as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->patient->name }}</td>
                                    <td>{{ $item->appointment_date->format('d M Y') }}</td>
                                    <td>{{ Str::limit($item->diagnosis, 30) ?: '-' }}</td>
                                    <td>
                                        @if ($item->prescription)
                                            <span class="badge bg-success">Written</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Not written</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('doctor.prescription', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Write
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-file-medical"></i>
                                            You have no accepted appointments waiting for a prescription.
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

        @php $existing = $appointment->prescription; @endphp

        <div class="row g-4">

            {{-- ---------- Patient card ---------- --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">

                        <div class="profile-avatar"><i class="bi bi-person"></i></div>

                        <h5>{{ $appointment->patient->name }}</h5>
                        <p class="text-muted mb-4">{{ $appointment->patient->user->email }}</p>

                        <table class="table text-start mb-0">
                            <tr><td class="text-muted">Age</td><td>{{ $appointment->patient->age }} years</td></tr>
                            <tr><td class="text-muted">Gender</td><td>{{ ucfirst($appointment->patient->gender) }}</td></tr>
                            <tr><td class="text-muted">Visit</td><td>{{ $appointment->appointment_date->format('d M Y') }}</td></tr>
                        </table>

                        <div class="alert alert-light border mt-3 text-start mb-0">
                            <strong>Diagnosis</strong><br>
                            {{ $appointment->diagnosis ?: 'Not written yet - please write it first.' }}
                        </div>

                    </div>
                </div>
            </div>

            {{-- ---------- Prescription builder ---------- --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">

                        <h5 class="mb-4">Write Prescription</h5>

                        @if (blank($appointment->diagnosis))
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Please <a href="{{ route('doctor.diagnosis', $appointment) }}">write the diagnosis</a>
                                before the prescription.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('doctor.prescription.store', $appointment) }}">
                            @csrf

                            <h6 class="mb-3">Medicines</h6>

                            <div id="medicineRows">
                                @php
                                    // When editing, show the medicines that are already saved.
                                    $rows = $existing && $existing->medicines->isNotEmpty()
                                        ? $existing->medicines
                                        : collect([null]);
                                @endphp

                                @foreach ($rows as $index => $row)
                                    <div class="medicine-row">
                                        <div class="row g-2 align-items-end">

                                            <div class="col-md-4">
                                                <label class="form-label">Medicine</label>
                                                <select name="medicines[{{ $index }}][medicine_id]"
                                                        class="form-select" required>
                                                    <option value="">Select medicine</option>
                                                    @foreach ($medicines as $medicine)
                                                        <option value="{{ $medicine->id }}"
                                                                @selected($row && $row->id === $medicine->id)>
                                                            {{ $medicine->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Dosage</label>
                                                <input type="text" name="medicines[{{ $index }}][dosage]"
                                                       class="form-control" placeholder="e.g. 500mg"
                                                       value="{{ $row?->pivot?->dosage }}" required>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Frequency</label>
                                                <select name="medicines[{{ $index }}][frequency]" class="form-select" required>
                                                    @foreach (['1x daily', '2x daily', '3x daily', '4x daily', 'When needed'] as $frequency)
                                                        <option value="{{ $frequency }}"
                                                                @selected($row?->pivot?->frequency === $frequency)>
                                                            {{ $frequency }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Duration</label>
                                                <input type="text" name="medicines[{{ $index }}][duration]"
                                                       class="form-control" placeholder="e.g. 5 days"
                                                       value="{{ $row?->pivot?->duration }}" required>
                                            </div>

                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-medicine-row">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="addMedicineRow" class="btn btn-outline-primary btn-sm mb-4">
                                <i class="bi bi-plus-lg"></i> Add Medicine
                            </button>

                            <div class="mb-4">
                                <label class="form-label" for="instructions">Instructions / Notes</label>
                                <textarea id="instructions" name="instructions" rows="3" class="form-control"
                                          placeholder="Additional instructions for the patient...">{{ old('instructions', $existing->instructions ?? '') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" @disabled(blank($appointment->diagnosis))>
                                <i class="bi bi-save"></i> Save Prescription
                            </button>

                        </form>

                    </div>
                </div>
            </div>

        </div>

        {{--
            The template below is copied by script.js every time the doctor
            clicks "Add Medicine". __INDEX__ is replaced with the row number.
        --}}
        <template id="medicineRowTemplate">
            <div class="medicine-row">
                <div class="row g-2 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Medicine</label>
                        <select name="medicines[__INDEX__][medicine_id]" class="form-select" required>
                            <option value="">Select medicine</option>
                            @foreach ($medicines as $medicine)
                                <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Dosage</label>
                        <input type="text" name="medicines[__INDEX__][dosage]" class="form-control"
                               placeholder="e.g. 500mg" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Frequency</label>
                        <select name="medicines[__INDEX__][frequency]" class="form-select" required>
                            @foreach (['1x daily', '2x daily', '3x daily', '4x daily', 'When needed'] as $frequency)
                                <option value="{{ $frequency }}">{{ $frequency }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Duration</label>
                        <input type="text" name="medicines[__INDEX__][duration]" class="form-control"
                               placeholder="e.g. 5 days" required>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100 remove-medicine-row">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                </div>
            </div>
        </template>

    @endif

@endsection
