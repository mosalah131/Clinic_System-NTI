@extends('layouts.app')

@section('title', 'Patient Files')

@section('content')

    <div class="mb-4">
        <h3>Patient Files</h3>
        <p class="text-muted mb-0">Upload reports, x-rays and scans for your patients</p>
    </div>

    <div class="row g-4">

        {{-- ---------- Upload form ---------- --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-4">Upload File</h5>

                    {{--
                        enctype="multipart/form-data" is REQUIRED whenever a form
                        carries a file. Without it the file never reaches the server.
                    --}}
                    <form method="POST" action="{{ route('doctor.files.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="e.g. Chest X-Ray" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Type</label>
                            <select name="file_type" class="form-select" required>
                                <option value="lab_result">Lab Result</option>
                                <option value="x_ray">X-Ray</option>
                                <option value="prescription_scan">Prescription Scan</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <div class="file-upload">
                                <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                <p class="text-muted mt-2 mb-2">
                                    PDF, JPG, PNG or DOCX &middot;
                                    max {{ round(config('clinic.uploads.max_size_kb') / 1024) }} MB
                                </p>
                                <input type="file" name="file" class="form-control"
                                       accept=".pdf,.jpg,.jpeg,.png,.docx" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"
                                      placeholder="Write a short description..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload"></i> Upload File
                        </button>

                    </form>

                </div>
            </div>
        </div>

        {{-- ---------- Uploaded files ---------- --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-3">Uploaded Files</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr><th>File</th><th>Patient</th><th>Type</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($files as $file)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            {{ $file->title }}
                                            @if ($file->description)
                                                <small class="d-block text-muted">{{ Str::limit($file->description, 40) }}</small>
                                            @endif
                                        </td>

                                        <td>{{ $file->patient->name }}</td>
                                        <td><span class="badge bg-light text-dark">{{ $file->type_label }}</span></td>
                                        <td>{{ $file->created_at->format('d M Y') }}</td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ $file->url }}" target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if ($file->uploaded_by === auth()->id())
                                                    <form method="POST" action="{{ route('doctor.files.destroy', $file) }}"
                                                          data-confirm="Delete the file &quot;{{ $file->title }}&quot;?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="bi bi-folder2-open"></i>
                                                No files have been uploaded yet.
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
