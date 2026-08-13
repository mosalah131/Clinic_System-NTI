<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PHASE 5 - Form Request Validation.
 *
 * A "Form Request" is a class whose only job is to check that the data sent by
 * a form is valid. Laravel runs it automatically BEFORE the controller method
 * starts, so by the time the controller runs, the data is guaranteed to be good.
 *
 * If something is wrong, Laravel sends the user back to the form with the error
 * messages - the controller is never even called.
 */
class RegisterRequest extends FormRequest
{
    /** Anybody (even a guest) is allowed to submit the register form. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20'],
            'dob'      => ['required', 'date', 'before:today'],
            'gender'   => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** Friendly messages instead of Laravel's technical defaults. */
    public function messages(): array
    {
        return [
            'name.required'      => 'Your full name is required.',
            'email.required'     => 'Email is required.',
            'email.email'        => 'Please enter a valid email address.',
            'email.unique'       => 'This email is already registered.',
            'phone.required'     => 'Phone number is required.',
            'dob.required'       => 'Date of birth is required.',
            'dob.before'         => 'The date of birth must be in the past.',
            'gender.required'    => 'Please choose a gender.',
            'password.required'  => 'Password is required.',
            'password.min'       => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ];
    }
}
