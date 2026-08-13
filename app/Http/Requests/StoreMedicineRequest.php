<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Validation for adding AND editing a medicine.
 *
 * The only difference between the two is the "unique" rule: when editing, the
 * medicine's own name must not count as a duplicate of itself.
 */
class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public function rules(): array
    {
        // On an update route the medicine is in the URL; on a create route it is not.
        $medicine = $this->route('medicine');

        return [
            'name'        => [
                'required', 'string', 'max:100',
                Rule::unique('medicines', 'name')->ignore($medicine?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'category'    => ['nullable', 'string', 'max:50'],
            'price'       => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'status'      => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'The medicine name is required.',
            'name.unique'       => 'This medicine already exists in the catalogue.',
            'price.required'    => 'Please enter a price (0 is allowed).',
            'quantity.required' => 'Please enter a quantity (0 is allowed).',
        ];
    }
}
