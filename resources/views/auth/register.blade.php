@extends('layouts.guest')

@section('title', 'Register')

@section('content')

    <div class="text-center mb-4">
        <div class="login-icon">
            <i class="bi bi-person-plus"></i>
        </div>

        <h2>Clinic System</h2>
        <p>Create your patient account</p>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="Enter your full name" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                   class="form-control @error('phone') is-invalid @enderror"
                   placeholder="Enter your phone number" required>
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label" for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" value="{{ old('dob') }}"
                       max="{{ now()->toDateString() }}"
                       class="form-control @error('dob') is-invalid @enderror" required>
                @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 mb-3">
                <label class="form-label" for="gender">Gender</label>
                <select id="gender" name="gender"
                        class="form-select @error('gender') is-invalid @enderror" required>
                    <option value="">Select</option>
                    <option value="male"   @selected(old('gender') === 'male')>Male</option>
                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="reg_password">Password</label>
            <div class="password-wrapper">
                <input type="password" id="reg_password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="At least 8 characters" required>
                <button type="button" class="password-toggle"><i class="bi bi-eye"></i></button>
            </div>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" placeholder="Confirm your password" required>
                <button type="button" class="password-toggle"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Create Account</button>

    </form>

    <div class="text-center mt-4">
        <p>
            Already have an account?
            <a href="{{ route('login') }}">Login</a>
        </p>
    </div>

@endsection
