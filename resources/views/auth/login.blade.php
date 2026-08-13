@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    <div class="text-center mb-4">
        <div class="login-icon">
            <i class="bi bi-hospital"></i>
        </div>

        <h2>Clinic System</h2>
        <p>Login to your account</p>
    </div>

    @include('partials.flash')

    {{-- @csrf adds a hidden security token. Laravel rejects any form without it. --}}
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>

            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email"
                   required
                   autofocus>

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>

            <div class="password-wrapper">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Enter your password"
                       required>

                <button type="button" class="password-toggle">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="role">Login As <span class="text-muted">(optional)</span></label>

            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                <option value="">Detect automatically</option>
                <option value="patient"   @selected(old('role') === 'patient')>Patient</option>
                <option value="doctor"    @selected(old('role') === 'doctor')>Doctor</option>
                <option value="reception" @selected(old('role') === 'reception')>Reception</option>
                <option value="admin"     @selected(old('role') === 'admin')>Admin</option>
            </select>

            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>

    </form>

    <div class="text-center mt-4">
        <p>
            Don't have an account?
            <a href="{{ route('register') }}">Create Account</a>
        </p>
    </div>

@endsection
