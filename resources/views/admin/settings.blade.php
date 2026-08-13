@extends('layouts.app')

@section('title', 'Settings')

@section('content')

    <div class="mb-4">
        <h3>Settings</h3>
        <p class="text-muted mb-0">Your account and system preferences</p>
    </div>

    <div class="row g-4">

        {{-- ---------- Profile ---------- --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <h5 class="mb-4"><i class="bi bi-person me-2"></i>Profile</h5>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>

                    </form>

                </div>
            </div>
        </div>

        {{-- ---------- Password ---------- --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <h5 class="mb-4"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="current_password" class="form-control"
                                       placeholder="Current password" required>
                                <button type="button" class="password-toggle"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" class="form-control"
                                       placeholder="At least 8 characters" required>
                                <button type="button" class="password-toggle"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Confirm password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Change Password</button>

                    </form>

                </div>
            </div>
        </div>

        {{-- ---------- Account info ---------- --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-4"><i class="bi bi-info-circle me-2"></i>Account</h5>

                    <table class="table mb-0">
                        <tr>
                            <td class="text-muted">Role</td>
                            <td><span class="badge bg-primary">{{ ucfirst($user->role) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member since</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>

        {{-- ---------- Appearance + session ---------- --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h5 class="mb-4"><i class="bi bi-display me-2"></i>Appearance & Session</h5>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="darkModeToggle">
                        <label class="form-check-label" for="darkModeToggle">Dark mode</label>
                    </div>

                    <form class="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> End session and log out
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection
