<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Validation for writing a prescription.
 *
 * "medicines.*.dosage" means: EVERY row of the medicines array must have a
 * dosage. The * is a wildcard for the row number.
 */
class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isDoctor();
    }

    public function rules(): array
    {
        return [
            'instructions'            => ['nullable', 'string', 'max:2000'],
            'medicines'               => ['required', 'array', 'min:1'],
            'medicines.*.medicine_id' => ['required', 'exists:medicines,id'],
            'medicines.*.dosage'      => ['required', 'string', 'max:50'],
            'medicines.*.frequency'   => ['required', 'string', 'max:50'],
            'medicines.*.duration'    => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'medicines.required'               => 'Please add at least one medicine.',
            'medicines.min'                    => 'Please add at least one medicine.',
            'medicines.*.medicine_id.required' => 'Please choose a medicine from the list.',
            'medicines.*.medicine_id.exists'   => 'That medicine is not in the clinic catalogue.',
            'medicines.*.dosage.required'      => 'Dosage is required for every medicine.',
            'medicines.*.frequency.required'   => 'Frequency is required for every medicine.',
            'medicines.*.duration.required'    => 'Duration is required for every medicine.',
        ];
    }
}
