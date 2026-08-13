<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * PHASE 1 - Authentication.
 *
 * Handles Register, Login and Logout for every role. There is only ONE login
 * page: after the password is verified we read the "role" column and send the
 * user to the dashboard that belongs to them.
 */
class AuthController extends Controller
{
    /* ==================================================================
     | LOGIN
     | =================================================================*/

    public function showLogin(): View|RedirectResponse
    {
        // Somebody who is already logged in has no business on the login page.
        if (Auth::check()) {
            return redirect(Auth::user()->homeRoute());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['nullable', 'in:admin,doctor,reception,patient'],
        ], [
            'email.required'    => 'Please enter your email address.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');

        // Auth::attempt() hashes the given password and compares it with the
        // hash stored in the database. If they match, a session is created.
        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $remember
        )) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        // An account the admin has switched off must not be able to get in.
        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Please contact the clinic.',
            ]);
        }

        // The "Login As" dropdown is only a convenience. If it was filled in and
        // does not match the real role we stop, so nobody is confused about
        // which dashboard they landed on.
        if (! empty($credentials['role']) && $credentials['role'] !== $user->role) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'role' => 'This account is not registered as a '.$credentials['role'].'.',
            ]);
        }

        $request->session()->regenerate();

        ActivityLog::record('auth.login', $user->name.' logged in.');

        return redirect()->intended($user->homeRoute())
                         ->with('success', 'Welcome back, '.$user->displayName().'!');
    }

    /* ==================================================================
     | REGISTER  (creates a PATIENT account)
     | =================================================================*/

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(Auth::user()->homeRoute());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        // RegisterRequest has already checked everything. If the data were bad
        // we would never have reached this line.
        $data = $request->validated();

        // Creating the login account AND the patient profile must either both
        // succeed or both fail - that is what a transaction guarantees.
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
                'password' => $data['password'],   // hashed automatically by the model
                'role'     => User::ROLE_PATIENT,
                'status'   => 'active',
            ]);

            Patient::create([
                'user_id' => $user->id,
                'dob'     => $data['dob'],
                'gender'  => $data['gender'],
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::record('auth.register', $user->name.' created a patient account.');

        return redirect()->route('patient.dashboard')
                         ->with('success', 'Your account has been created. Welcome!');
    }

    /* ==================================================================
     | LOGOUT
     | =================================================================*/

    public function logout(Request $request): RedirectResponse
    {
        if ($user = Auth::user()) {
            ActivityLog::record('auth.logout', $user->name.' logged out.');
        }

        Auth::logout();

        // Destroy the session completely so the back button cannot restore it.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
