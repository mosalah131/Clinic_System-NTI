<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Validation for booking an appointment.
 *
 * The patient books for themselves, so they do not send a patient_id.
 * Reception and the admin book on behalf of somebody, so they must.
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only these three roles may create an appointment (the doctor may not).
        return Auth::check() && in_array(Auth::user()->role, ['patient', 'reception', 'admin'], true);
    }

    public function rules(): array
    {
        $rules = [
            'doctor_id'        => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'symptoms'         => ['nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];

        if (! Auth::user()->isPatient()) {
            $rules['patient_id'] = ['required', 'exists:patients,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'patient_id.required'             => 'Please choose a patient.',
            'doctor_id.required'              => 'Please choose a doctor.',
            'appointment_date.required'       => 'Please choose a date.',
            'appointment_date.after_or_equal' => 'You cannot book an appointment in the past.',
            'appointment_time.required'       => 'Please choose a time.',
        ];
    }
}
