@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

    <div class="mb-4">
        <h3>My Profile</h3>
        <p class="text-muted mb-0">Your personal and professional information</p>
    </div>

    <div class="row g-4">

        {{-- ---------- Summary card ---------- --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">

                    <div class="profile-avatar"><i class="bi bi-person"></i></div>

                    <h4>{{ $doctor->display_name }}</h4>
                    <p class="text-muted">{{ $doctor->specialization }}</p>

                    <div class="row text-center mt-4">
                        <div class="col-4">
                            <h6 class="mb-0">{{ $stats['patients'] }}</h6>
                            <small class="text-muted">Patients</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ $stats['appointments'] }}</h6>
                            <small class="text-muted">Appointments</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ $stats['completed'] }}</h6>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>

                    <hr>

                    <table class="table text-start mb-0">
                        <tr><td class="text-muted">Department</td><td>{{ $doctor->department->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Fee</td><td>{{ number_format($doctor->consultation_fee, 2) }}</td></tr>
                        <tr><td class="text-muted">Joined</td><td>{{ $user->created_at->format('M Y') }}</td></tr>
                    </table>

                </div>
            </div>
        </div>

        {{-- ---------- Edit form ---------- --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="mb-4">Personal & Professional Information</h5>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $user->phone) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select" required>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                                @selected(old('department_id', $doctor->department_id) == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" class="form-control"
                                       value="{{ old('specialization', $doctor->specialization) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Consultation Fee</label>
                                <input type="number" step="0.01" min="0" name="consultation_fee" class="form-control"
                                       value="{{ old('consultation_fee', $doctor->consultation_fee) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" rows="3" class="form-control"
                                          placeholder="Short professional summary">{{ old('bio', $doctor->bio) }}</textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary mt-4">Save Changes</button>

                    </form>

                </div>
            </div>

            {{-- ---------- Password ---------- --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-4">Change Password</h5>

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">Change Password</button>

                    </form>

                </div>
            </div>

        </div>

    </div>

@endsection
